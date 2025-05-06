<?php
namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Session;

class RestaurantIdFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['url']) && strpos($item['url'], '{restaurantId}') !== false) {
            $restaurantId = Session::get('restaurante_id');
            if ($restaurantId) {
                $item['url'] = str_replace('{restaurantId}', $restaurantId, $item['url']);
            } else {
                // Si no hay restaurant_id en sesión, se puede definir una URL por defecto o dejar
                $item['url'] = '#';
            }
        }
        return $item;
    }
}