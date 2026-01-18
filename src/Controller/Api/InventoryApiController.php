<?php

namespace App\Controller\Api;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class InventoryApiController extends AbstractController
{
    #[Route('/api/inventory/{token}/stats', name: 'api_inventory_stats', methods: ['GET'])]
    public function getStats(string $token, InventoryRepository $repo): JsonResponse
    {
        $inventory = $repo->findOneBy(['api_token' => $token]);

        if (!$inventory) {
            return $this->json(['error' => 'Invalid API Token'], 404);
        }

        return $this->json([
            'inventory_title' => $inventory->getTitle(),
            'owner'           => $inventory->getCreator() ? $inventory->getCreator()->getUserIdentifier() : 'Unknown',
            'item_count'      => count($inventory->getItems()),
            'stats'           => $this->calculateAggregations($inventory),
        ]);
    }

    private function calculateAggregations(Inventory $inventory): array
    {
        $items = $inventory->getItems();

        if (count($items) === 0) {
            return [
                'message' => 'No items in this inventory',
                'numeric_aggregations' => ['min_value' => 0, 'max_value' => 0, 'average_value' => 0],
                'text_aggregations' => ['most_popular_tags' => []]
            ];
        }

        $prices = [];
        $tags = [];

        foreach ($items as $item) {
            if (method_exists($item, 'getIntVal1') && $item->getIntVal1() !== null) {
                $prices[] = (float)$item->getIntVal1();
            }

            if (method_exists($item, 'getTags') && is_array($item->getTags())) {
                foreach ($item->getTags() as $tag) {
                    $tags[] = (string)$tag;
                }
            }
        }

        $min = !empty($prices) ? min($prices) : 0;
        $max = !empty($prices) ? max($prices) : 0;
        $avg = !empty($prices) ? array_sum($prices) / count($prices) : 0;

        $tagCounts = array_count_values($tags);
        arsort($tagCounts);
        $popularTags = array_slice(array_keys($tagCounts), 0, 3);

        return [
            'numeric_aggregations' => [
                'min_value' => $min,
                'max_value' => $max,
                'average_value' => round($avg, 2),
            ],
            'text_aggregations' => [
                'most_popular_tags' => $popularTags,
            ]
        ];
    }
}