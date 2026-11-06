<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Liste des catégories (F-PROD-02).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with('children')
            ->withCount('products')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Création d'une catégorie ou sous-catégorie.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'category' => $category->load('parent'),
        ], 201);
    }

    /**
     * Détails d'une catégorie.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'category' => $category->load(['children', 'parent'])->loadCount('products'),
        ]);
    }

    /**
     * Mise à jour d'une catégorie.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $slug = Str::slug($validated['name']);
            if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }
            $validated['slug'] = $slug;
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'category' => $category->fresh(['parent', 'children']),
        ]);
    }

    /**
     * Suppression d'une catégorie.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette catégorie car des produits y sont encore rattachés.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }
}
