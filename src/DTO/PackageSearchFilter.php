<?php

namespace App\DTO;

use App\Entity\Category;

class PackageSearchFilter
{
    public ?string $name = null;
    public ?string $minPrice = null;
    public ?string $maxPrice = null;

    public ?Category $category = null;

    public ?bool $isAvailable = true;

    //TODO: more filters: city, business, etc.
    }
