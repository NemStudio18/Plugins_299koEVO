<?php

namespace Ecommerce\Entities;

use Utils\Util;

defined('ROOT') or exit('Access denied!');

class ProductManager
{
    public function __construct()
    {
        $this->autoMigrateLegacyIds();
    }

    private function autoMigrateLegacyIds(): void
    {
        $file = DATA_PLUGIN . 'ecommerce/products.json';
        if (!file_exists($file)) {
            return;
        }
        $raw = @file_get_contents($file);
        if (!$raw) {
            return;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data)) {
            return;
        }
        $needsMigration = false;
        $maxId = 0;
        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if ($id === null || !is_numeric($id)) {
                $needsMigration = true;
                break;
            }
            $maxId = max($maxId, (int)$id);
        }
        if (!$needsMigration) {
            return;
        }
        $nextId = $maxId + 1;
        $migrated = [];
        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if ($id === null || !is_numeric($id)) {
                $item['id'] = $nextId++;
            } else {
                $item['id'] = (int)$id;
            }
            $migrated[] = $item;
        }
        @file_put_contents($file, json_encode($migrated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function getAll(bool $activeOnly = false): array
    {
        $all = Product::queryBuilder()->orderBy('created_at', 'DESC')->get();
        if ($activeOnly) {
            $all = array_filter($all, function ($prod) {
                return $prod->isActive();
            });
        }
        return array_values($all);
    }

    public function getById($id): ?Product
    {
        if ($id === null || $id === '') {
            return null;
        }
        $product = Product::findPK($id);
        if ($product) {
            return $product;
        }
        if (is_numeric($id)) {
            $product = Product::findPK((int)$id) ?? Product::findPK((string)$id);
            if ($product) {
                return $product;
            }
        }
        // Fallback ultime : scanner tous les produits
        $all = Product::all();
        foreach ($all as $item) {
            $itemId = $item->attributes['id'] ?? null;
            if ((string)$itemId === (string)$id) {
                return $item;
            }
        }
        return null;
    }

    public function getBySlug(string $slug): ?Product
    {
        return Product::find('slug', $slug);
    }

    public function getByCategory(int $categoryId, bool $activeOnly = false): array
    {
        $products = Product::queryBuilder()->where('category_id', '=', $categoryId)->orderBy('created_at', 'DESC')->get();
        if ($activeOnly) {
            $products = array_filter($products, function ($prod) {
                return $prod->isActive();
            });
        }
        return array_values($products);
    }

    public function create(array $data): ?Product
    {
        if (empty($data['slug'])) {
            $data['slug'] = Util::strToUrl($data['name']);
        }
        
        $product = new Product($data);
        if ($product->save()) {
            return $product;
        }
        return null;
    }

    public function update($id, array $data): bool
    {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Util::strToUrl($data['name']);
        }
        
        foreach ($data as $key => $value) {
            $product->{$key} = $value;
        }
        
        return $product->save();
    }

    public function delete($id): bool
    {
        $product = $this->getById($id);
        return $product ? $product->delete() : false;
    }

    public function decreaseStock(int $id, int $quantity): bool
    {
        $product = $this->getById($id);
        if (!$product || $product->getStock() < $quantity) {
            return false;
        }
        
        $product->stock = $product->getStock() - $quantity;
        return $product->save();
    }
}

