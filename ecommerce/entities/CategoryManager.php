<?php

namespace Ecommerce\Entities;

use Utils\Util;

defined('ROOT') or exit('Access denied!');

class CategoryManager
{
    public function getAll(): array
    {
        return Category::queryBuilder()->orderBy('name', 'ASC')->get();
    }

    public function getById($id): ?Category
    {
        if (empty($id)) {
            return null;
        }
        $cat = Category::findPK($id);
        if (!$cat && is_numeric($id)) {
            $cat = Category::findPK((int)$id) ?? Category::findPK((string)$id);
        }
        return $cat;
    }

    public function getBySlug(string $slug): ?Category
    {
        return Category::find('slug', $slug);
    }

    public function create(array $data): ?Category
    {
        if (empty($data['slug'])) {
            $data['slug'] = Util::strToUrl($data['name']);
        }
        
        $category = new Category($data);
        if ($category->save()) {
            return $category;
        }
        return null;
    }

    public function update($id, array $data): bool
    {
        $category = $this->getById($id);
        if (!$category) {
            return false;
        }
        
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Util::strToUrl($data['name']);
        }
        
        foreach ($data as $key => $value) {
            $category->{$key} = $value;
        }
        
        return $category->save();
    }

    public function delete($id): bool
    {
        $category = $this->getById($id);
        return $category ? $category->delete() : false;
    }
}

