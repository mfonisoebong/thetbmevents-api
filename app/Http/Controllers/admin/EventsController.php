<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Event;
use App\Models\EventCategory;
use App\Traits\HttpResponses;
use App\Traits\StoreImage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class EventsController extends Controller
{
    use HttpResponses, StoreImage;


    public function getAllEvents()
    {
        $events = Event::all(['id', 'title', 'image_url']);
        return $this->success($events);
    }

    public function getCategories()
    {
        $categories = EventCategory::filter()
            ->paginate();
        $categoriesArray = $categories->toArray();
        $data = [
            ...$categoriesArray,
            'data' => CategoryResource::collection($categoriesArray['data'])
        ];

        return $this->success($data);
    }

    public function store(StoreCategoryRequest $request)
    {
        $request->validated($request->all());
        $iconFileName = $request->file('icon') ?
            'storage/events-categories/' . Str::uuid()->toString() . '.webp' : null;

        if ($iconFileName) {
            $this->storeImage($iconFileName, null, $request->file('icon'));
        }
        $data = [
            ...$request->except('icon'),
            'icon' => $iconFileName
        ];

        $category = EventCategory::create($data);
        return $this->success(new CategoryResource($category), 'Event category created');
    }


    public function update(EventCategory $category, UpdateCategoryRequest $request)
    {
        $request->validated($request->all());

        $iconFileName = $request->file('icon') ?
            'storage/events-categories/' . Str::uuid()->toString() . '.webp' : $category->icon;

        if ($request->file('icon')) {
            $this->storeImage($iconFileName, $category->icon, $request->file('icon'));
        }

        $data = [
            ...$request->except('icon'),
            'icon' => $iconFileName
        ];

        $category->update($data);

        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(EventCategory $category)
    {
        $category->delete();
        return $this->success(null, 'Category deleted successfully');
    }

    public function removeIcon(EventCategory $category)
    {
        $this->removeFile($category->icon);
        $category->update(['icon' => null]);
        return $this->success(null, 'Avatar removed successfull');
    }


}
