<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = EventCategory::orderBy('category')
            ->get()
            ->map(function (EventCategory $category) {
                $category->events_count = Event::where('category', 'like', '%'.$category->category.'%')->count();
                return $category;
            });

        return $this->success(
            CategoryResource::collection($categories),
            'Categories fetched successfully'
        );
    }

    public function createCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $name = trim($validated['name']);

        $category = EventCategory::create([
            'category' => $name,
            'slug' => Str::slug($name),
        ]);

        return $this->success($category, 'Category created successfully');
    }

    public function deleteCategory(EventCategory $category)
    {
        $category->delete();
        return $this->success(null, 'Category deleted successfully');
    }
}
