<?php

/**
 * Bootstrap 5.3 WordPress Nav Walker
 * 
 * Optimizovan i potpuno siguran nav walker za Bootstrap 5.3.x
 * 
 * @package StarWars
 * @since 1.0.0
 */

class bootstrap_5_wp_nav_menu_walker extends Walker_Nav_menu
{
  private $current_item;
  private $dropdown_menu_alignment_values = [
    'dropdown-menu-start',
    'dropdown-menu-end',
    'dropdown-menu-sm-start',
    'dropdown-menu-sm-end',
    'dropdown-menu-md-start',
    'dropdown-menu-md-end',
    'dropdown-menu-lg-start',
    'dropdown-menu-lg-end',
    'dropdown-menu-xl-start',
    'dropdown-menu-xl-end',
    'dropdown-menu-xxl-start',
    'dropdown-menu-xxl-end'
  ];

  /**
   * Starts the list before the elements are added.
   */
  function start_lvl(&$output, $depth = 0, $args = [])
  {
    $dropdown_menu_class[] = '';
    foreach ($this->current_item->classes as $class) {
      if (in_array($class, $this->dropdown_menu_alignment_values)) {
        $dropdown_menu_class[] = $class;
      }
    }
    $indent = str_repeat("\t", $depth);
    $submenu = ($depth > 0) ? ' sub-menu' : '';
    $output .= "\n$indent<ul class=\"dropdown-menu$submenu " . esc_attr(implode(" ", $dropdown_menu_class)) . " depth_$depth\">\n";
  }

  /**
   * Starts the element output.
   */
  function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
  {
    $this->current_item = $item;

    $indent = ($depth) ? str_repeat("\t", $depth) : '';

    $li_attributes = '';
    $class_names = $value = '';

    $classes = empty($item->classes) ? array() : (array) $item->classes;

    // Provera da li stavka ima decu - sigurna verzija
    $has_children = false;
    if (is_object($args) && isset($args->walker)) {
      $has_children = (property_exists($args->walker, 'has_children') && $args->walker->has_children);
    } elseif (is_array($args) && isset($args['walker'])) {
      $has_children = (property_exists($args['walker'], 'has_children') && $args['walker']->has_children);
    }

    $classes[] = $has_children ? 'dropdown' : '';
    $classes[] = 'nav-item';
    $classes[] = 'nav-item-' . $item->ID;
    if ($depth && $has_children) {
      $classes[] = 'dropdown-menu dropdown-menu-end';
    }

    $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
    $class_names = ' class="' . esc_attr($class_names) . '"';

    $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
    $id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';

    $output .= $indent . '<li ' . $id . $value . $class_names . $li_attributes . '>';

    $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
    $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
    $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
    $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

    // POUZDANO REŠENJE ZA ODREĐIVANJE AKTIVNE STAVKE
    $active_class = '';

    // Dobavljanje trenutne URL putanje
    global $wp;
    $current_url = home_url($wp->request);
    $current_url = rtrim($current_url, '/') . '/';

    // URL stavke menija sa završnim /
    $menu_url = rtrim($item->url, '/') . '/';

    // Provera da li smo na početnoj stranici
    $is_front = is_front_page() || empty($wp->request);

    // Da li je ova stavka link za početnu stranicu
    $is_home_link = rtrim(home_url('/'), '/') . '/' === $menu_url;

    // Odlučivanje kada primeniti aktivnu klasu
    if (
      // Stavka je link na početnu I nalazimo se na početnoj
      ($is_home_link && $is_front) ||
      // Stavka tačno odgovara trenutnoj stranici (za sve ostale stranice)
      (!$is_home_link && $current_url === $menu_url) ||
      // Za roditelje u dropdown menijima, samo kada NISMO na početnoj
      (!$is_front && (in_array('current-menu-ancestor', $classes) || in_array('current-menu-parent', $classes)))
    ) {
      $active_class = 'active';
    }

    $nav_link_class = ($depth > 0) ? 'dropdown-item ' : 'nav-link ';

    // Dodajemo data-bs-toggle i data-bs-auto-close atribute za Bootstrap 5.3 dropdown
    if ($has_children) {
      $attributes .= ' class="' . $nav_link_class . $active_class . ' dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"';
    } else {
      $attributes .= ' class="' . $nav_link_class . $active_class . '"';
    }

    // Sigurna obrada argumenta before, after, link_before, link_after
    $before = '';
    $after = '';
    $link_before = '';
    $link_after = '';

    if (is_object($args)) {
      if (isset($args->before)) {
        $before = $args->before;
      }
      if (isset($args->after)) {
        $after = $args->after;
      }
      if (isset($args->link_before)) {
        $link_before = $args->link_before;
      }
      if (isset($args->link_after)) {
        $link_after = $args->link_after;
      }
    } elseif (is_array($args)) {
      if (isset($args['before'])) {
        $before = $args['before'];
      }
      if (isset($args['after'])) {
        $after = $args['after'];
      }
      if (isset($args['link_before'])) {
        $link_before = $args['link_before'];
      }
      if (isset($args['link_after'])) {
        $link_after = $args['link_after'];
      }
    }

    $item_output = $before;
    $item_output .= '<a' . $attributes . '>';
    $item_output .= $link_before . apply_filters('the_title', $item->title, $item->ID) . $link_after;
    $item_output .= '</a>';
    $item_output .= $after;

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }
}
