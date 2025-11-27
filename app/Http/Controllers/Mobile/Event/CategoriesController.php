<?php

namespace App\Http\Controllers\Mobile\Event;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Event\CategoryResource;
use App\Models\EventCategory;
use App\Traits\Pagination;

class CategoriesController extends Controller
{
    use Pagination;

    public function viewAll()
    {
        $categories = EventCategory::paginate(12);
        $list = CategoryResource::collection($categories);
        $data = $this->paginatedData($categories, $list);

        return $this->success($data);
    }
}
