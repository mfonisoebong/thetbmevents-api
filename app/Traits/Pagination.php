<?php

namespace App\Traits;

trait Pagination
{

    public function paginatedData($paginated, $list)
    {

        return [
            'data' => $list,
            'meta' => [
                'total' => $paginated->total(),
                'currentPage' => $paginated->currentPage(),
                'perPage' => $paginated->perPage(),
                'lastPage' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem()
            ]
        ];

    }

}
