<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Traits\HttpResponses;
use Illuminate\Routing\Controller;

class EventsController extends Controller
{
    use HttpResponses;


    public function getAllEvents(){
        $events= Event::all(['id','title', 'logo']);
        return $this->success($events);
    }
    
    public function getCategories(){
        $categories= EventCategory::filter()
        ->paginate();

        return $this->success($categories);
    }

    public function store(StoreCategoryRequest $request){
        $request->validated($request->all());
        $category= EventCategory::create($request->all());
        return $this->success($category, 'Event category created');
    }


    public function update(EventCategory $category, StoreCategoryRequest $request){
        $request->validated($request->all());

        $category->update($request->all());

        return $this->success($category, 'Category updated successfully');
    }

    public function destroy(EventCategory $category){
        $category->delete();
        return $this->success(null, 'Category deleted successfully');
    }

    
}
