<?php
class IconHelper
{
    public static function getIcon($name)
    {
        $n = strtolower($name);

        // Comprehensive Icon Mapping
        $icons = [
            'laundry' => 'fa-tshirt',
            // 'wash' removed to avoid duplicate key (see line 47)
            'iron' => 'fa-tshirt',
            'dry clean' => 'fa-tshirt',

            'pest' => 'fa-bug',
            'insect' => 'fa-bug',

            'carpenter' => 'fa-hammer',
            'wood' => 'fa-hammer',
            'furniture' => 'fa-chair',

            'garden' => 'fa-leaf',
            'plant' => 'fa-seedling',
            'lawn' => 'fa-leaf',

            'paint' => 'fa-paint-roller',
            'wall' => 'fa-palette',

            'sofa' => 'fa-couch',
            'upholstery' => 'fa-couch',

            'ro ' => 'fa-bottle-water',
            'purifier' => 'fa-bottle-water',
            'water' => 'fa-tint',

            'cctv' => 'fa-video',
            'camera' => 'fa-video',
            'security' => 'fa-shield-alt',

            'ac ' => 'fa-snowflake',
            'air cond' => 'fa-snowflake',
            'cool' => 'fa-snowflake',
            'fridge' => 'fa-snowflake',

            'car' => 'fa-car',
            'vehicle' => 'fa-car',
            'wash' => 'fa-car',

            'electric' => 'fa-bolt',
            'power' => 'fa-plug',
            'light' => 'fa-lightbulb',
            'fan' => 'fa-fan',

            'plumb' => 'fa-wrench',
            'pipe' => 'fa-faucet',
            'leak' => 'fa-fill-drip',

            'clean' => 'fa-broom',
            'maid' => 'fa-broom',
            'dust' => 'fa-sparkles',
            'housekeeping' => 'fa-pump-soap',

            'internet' => 'fa-wifi',
            'wifi' => 'fa-wifi',
            'network' => 'fa-network-wired',

            'cook' => 'fa-utensils',
            'chef' => 'fa-hat-chef',
            'food' => 'fa-burger',

            'fit' => 'fa-dumbbell',
            'gym' => 'fa-dumbbell',
            'yoga' => 'fa-om'
        ];

        foreach ($icons as $keyword => $icon) {
            if (strpos($n, $keyword) !== false) {
                return $icon;
            }
        }

        // Default Icon
        return 'fa-concierge-bell';
    }
}
?>