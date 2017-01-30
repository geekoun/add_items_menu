# Wordpress

This php function can create a menu and add items

## Install

Add function in functions.php in the Wordpress theme

## Usage

This exemple Auto install an menu :

```php
$name = 'Main menu';

$args = array(
  array(
    'ID' => #ID_PAGE,
    'type' => 'post_type',
    'object' => 'page',
    'menu_order' => 1
  ),

  array(
    'ID' => '-99',
    'type' => 'post_type_archive',
    'object' => 'project',
    'menu_order' => 2
  ),
  
    array(
    'ID' => #ID_CATEGORY,
    'type' => 'taxonomy',
    'object' => 'category',
    'menu_order' => 3
  ),
 );
 
 $location = array(
  'bool' => true, //true for modify location
  'slug' => 'header-menu' //if new location menu add slug else nothing ''
);

wp_set_items_menu($name, $args, $location);
```
## Works

Do not hesitate to improve this function
