<?php
namespace B2\Modules\Templates;

use B2\Modules\Common\Post;

class Menu extends \Walker_Nav_Menu {
	/**
	 * What the class handles.
	 *
	 * @since 3.0.0
	 * @var string
	 *
	 * @see Walker::$tree_type
	 */
	public $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * Database fields to use.
	 *
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 *
	 * @see Walker::$db_fields
	 */
    public $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
    
	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );
        $classes = array();
        // Default class.
        if($args->walker->has_children && $depth == 0){
            $classes = array('sub-menu-0','sub-menu','b2-radius');
        }
		

		/**
		 * Filters the CSS class(es) applied to a menu list element.
		 *
		 * @since 4.8.0
		 *
		 * @param array    $classes The CSS classes that are applied to the menu `<ul>` element.
		 * @param stdClass $args    An object of `wp_nav_menu()` arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
        $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$output .= "{$n}{$indent}<ul$class_names>{$n}";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );
		$output .= "$indent</ul>{$n}";
	}

	/**
	 * Starts the element output.
	 *
	 * @since 3.0.0
	 * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
        $current = array("current-menu-item");
        $classes = array_intersect($classes,$current);

		//添加菜单深度的类
        if($item->menu_item_parent == 0 && $depth == 0){
			$classes[] = 'depth-0';
		}
		
		//添加子菜单的类
        if($args->walker->has_children && $depth == 0){
			$child_item_type = b2_get_menu_option($item->ID,'menu_type');
            if($child_item_type){
                $classes[] = ' has_children b2-'.$child_item_type;
            }
		}
		
		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param WP_Post  $item  Menu item data object.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );
        
		/**
		 * Filters the CSS class(es) applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array    $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filters the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$color = '#607d8b';
		if($item->menu_item_parent == 0 && $depth == 0){
			
			$color = b2_get_menu_option($item->ID,'menu_color');

			if($item->object === 'category'){
				$_color = get_term_meta($item->object_id,'b2_tax_color',true);
				$color = $_color ? $_color : $color;
			}

			$output .= $indent . '<li ' . $class_names .'>';
		}else{
			$output .= $indent . '<li ' . $class_names .' >';
		}
		

        $atts = array();

        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title  Title attribute.
		 *     @type string $target Target attribute.
		 *     @type string $rel    The rel attribute.
		 *     @type string $href   The href attribute.
		 * }
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filters a menu item's title.
		 *
		 * @since 4.4.0
		 *
		 * @param string   $title The menu item's title.
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$menu_type = b2_get_menu_option($item->menu_item_parent,'menu_type');

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		
		if($item->menu_item_parent == 0 && $depth == 0){
			$item_output .= '<span class="hob" style="background-color:'.$color.'"></span>';
		}

		//如果是menu-1形式的菜单，显示菜单图标
		if($depth > 0){
			if($menu_type == 'menu-1'){
				$img = b2_get_menu_option($item->ID,'menu_img');
				$thumb = b2_get_thumb(array('thumb'=>$img,'width'=>120,'height'=>120));
				
				$item_output .= b2_get_img(array('src'=>$thumb,'alt'=>$title,'class'=>array('menu-1-img','b2-radius'),'attr'=>array('width'=>40,'height'=>40)));
			}
		}

		$child_item_type = b2_get_menu_option($item->menu_item_parent,'menu_type');

		//如果拥有下级菜单，增加图标
        if($args->walker->has_children){
			if($depth == 1 && $menu_type != 'menu-1'){
				$item_output .= $args->link_before . $title.b2_get_icon('b2-arrow-right-s-line'). $args->link_after;
			}elseif($depth == 0){
				$item_output .= $args->link_before . $title.b2_get_icon('b2-arrow-down-s-line'). $args->link_after;
			}

        }else{
			$m_parent = get_post_meta($item->menu_item_parent,'_menu_item_menu_item_parent',true);
			$menu_4 = b2_get_menu_option($m_parent,'menu_type');
			if($m_parent && $menu_4 === 'menu-4'){
				$img = b2_get_menu_option($item->ID,'menu_img');

				$thumb = b2_get_thumb(array('thumb'=>$img,'width'=>50,'height'=>50));
				$item_output .= $args->link_before . b2_get_img(array('src'=>$thumb,'alt'=>$title,'class'=>array('b2-radius'))).'<span>'.$title . '</span>'.$args->link_after;
			}else{
			
				
				$_item_output = $args->link_before . '<span>'.$title . '</span>'.$args->link_after;

				if($menu_type == 'menu-1'){
					if($item->object_id){
						$term = get_term($item->object_id);
						if($term){
							$_item_output = $args->link_before . '<span>'.$title . '</span><p>'.sprintf(__('%s篇','b2'),$term->count).'</p>'.$args->link_after;
						}
						
					}
				}

				$item_output .= $_item_output;
				
			}
            
		}

		$item_output .= '</a>';
		$item_output .= $args->after;

		//如果是menu-2
		if($depth == 1){
            if($child_item_type == 'menu-2'){
				$item_output .= '<div class="menu_2-item">'.$this->menu_2($item).'</div>';
			}
		}

		/**
		 * Filters a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $item_output The menu item's starting HTML output.
		 * @param WP_Post  $item        Menu item data object.
		 * @param int      $depth       Depth of menu item. Used for padding.
		 * @param stdClass $args        An object of wp_nav_menu() arguments.
         */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Page data object. Not used.
	 * @param int      $depth  Depth of page. Not Used.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$output .= "</li>{$n}";
	}

	public function menu_2($item){

		if(!isset($item->_menu_item_object)) return;

		$type = $this->get_post_type($item->_menu_item_object);

		$args = array(
			'post_type'=>$type,
			'posts_per_page'=>4,
			'paged'	=> 0,
			'no_found_rows'=>true
		);

		if($type == 'post' && isset($item->object_id)){
			$args['cat'] = $item->object_id;
		}

		$args = apply_filters('b2_menu_2_post_args',$args);
		$the_query = new \WP_Query( $args );
		$html = '';

		$size = array(
			'width'=>310,
			'height'=>192
		);

		$size = apply_filters('b2_menu_2_thumb_size', $size,$item->object_id);

		if ( $the_query->have_posts() ) {
			$html .= '<ul class="menu-2-'.$item->object_id.'">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$link = get_permalink();
				$title = get_the_title();
				$thumb = b2_get_thumb(array('thumb'=>Post::get_post_thumb($the_query->post->ID),'type'=>'fill','width'=>$size['width'],'height'=>$size['height']));
				$html .= '<li><div class="menu-post-box">
					<div class="menu-post-box-img">
						<a href="'.$link.'" class="b2-radius">
							'.b2_get_img(array('src'=>$thumb,'alt'=>$title,'class'=>array('menu-post-thumb','b2-radius'))).'
						</a>
					</div>
					<h2><a href="'.$link.'">'. $title.'</a></h2>
				</div></li>';
			}
			$html .= '</ul>';
			
		} else {
			$html .= '<div class="">'.__('没有文章','b2').'</div>';
		}
		wp_reset_postdata();
		return $html;
	}

	public function get_post_type($item_boject){

		$type = 'post';
		switch($item_boject){
			case 'category';
				$type = 'post';
			break;
			case 'shoptype';
				$type = 'shop';
			break;
			case 'labtype';
				$type = 'labs';
			case 'mp';
				$type = 'pps';
			break;
		}

		return $type;
	}

}