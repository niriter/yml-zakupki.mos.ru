<?php
/**
 * Plugin Name: Yml for zakupki.mos.ru
 * Plugin URI: https://github.com/niriter/yml-zakupki.mos.ru
 * Description: Custom yml for zakupki.mos.ru
 * Version: 0.1
 * Author: Nikita Kasianenko
 * Author URI: https://github.com/niriter
 */


function searcher($query, $search){
  if (preg_match("/{$search}/i", $query)) {
    return true;
  } else {
    return false;
  }
}

function custom_yml(){
  if (is_404()){
    global $wp;
    $url = str_replace('https://', '', home_url($wp->request));
    $url = str_replace('http://', '', $url);
    $url = explode('/', $url);
    var_dump($url);
    if ( $url[1] == 'zakupki.yml') {
      status_header(200);
      ?>
      <?php header ("Content-Type:text/xml"); ?>
      <?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
      <yml_catalog date="<?php echo date('Y-m-d h:i', time()); ?>">
        <shop>
          <name><?php echo get_bloginfo('name'); ?></name>
          <company><?php echo get_bloginfo('name'); ?></company>
          <url><?php echo home_url(''); ?>
          <currencies>
            <currency id="<?php echo get_woocommerce_currency(); ?>" rate="1"/>
          </currencies>
          <categories>
            <?php
              foreach (get_categories(array('taxonomy' => 'product_cat')) as $cat) {
                echo '<category id="'.$cat->term_id.'">'.$cat->name.'</category>'."\r\n";
              }
            ?>
          </categories>
          <delivery-options>
            <option days="2-5" cost="0"></option>
          </delivery-options>
          <offers>
            <?php
              $loop = new WP_Query(array('post_type' => 'product', 'posts_per_page' => 10000));
              while ($loop->have_posts()):
                $loop->the_post();
                global $product;
                if ($product->is_in_stock() == 1){ $avai = 'true'; } else { $avai = 'false'; }
                echo '<offer id="'.get_the_ID().'" available="'.$avai.'" bid="80">'."\r\n";
                echo '<currencyId>'.get_woocommerce_currency().'</currency>'."\r\n";
                echo '<name>'.wp_strip_all_tags(get_the_title()).'</name>'."\r\n";
                echo '<picture>'.wp_get_attachment_url($product->get_image_id()).'</picture>'."\r\n";
                echo '<isVisibleToStateCustomers>true</isVisibleToStateCustomers>'."\r\n";
                echo '<isAvailableToIndividuals>true</isAvailableToIndividuals>'."\r\n";
                foreach (get_the_terms($product->cat_ID , 'product_cat') as $term_cat) {
                  echo '<categoryId>'.$term_cat->term_id.'</categoryId>'."\r\n";
                }
                echo '<okei id="778">Упаковка</okei>'."\r\n";
                echo '<min-quantity>1</min-quantity>'."\r\n";
                echo '<max-quantity>100</max-quantity>'."\r\n";
                echo '<beginDate>'.date('Y-m-dTh:i', time() - 60 * 60 * 24).'</beginDate>'."\r\n";
                echo '<endDate>'.date('Y-m-dTh:i', time() + 60 * 60 * 24 * 7).'</endDate>'."\r\n";
                echo '<packageType id="4" />'."\r\n";
                echo '<region id="504">Москва</region>'."\r\n";
                if ($product->is_type('variable')){
                    foreach ($product->get_available_variations() as $key => $value)
                    {
                        echo 'key:'.$key.' value:'.$value;
                    }
                }
                $attributes = $product->get_attributes();
                foreach ($attributes as $attribute){
                  echo '<param name="'.wc_attribute_label($attribute['name']).'">'.wc_get_product_terms( $product->id, $attribute['name'], array('fields'=>'names'))[0].'</param>'."\r\n";
                }
                echo '<price>'.$product->get_price().'</price>';
                // echo '<categoryId>'.$product->cat_ID.'</categoryId>'."\r\n";
                // echo '<br /><a href="'.get_permalink().'">' . woocommerce_get_product_thumbnail().' '.get_the_title().'</a>';
                echo '</offer>'."\r\n";
              endwhile;
              wp_reset_query();
            ?>
          </offers>
          <gifts>

          </gifts>
          <promos>

          </promos>
        </shop>
      </yml_catalog>
    <?php
    }
  }
}

add_action( 'template_redirect', 'custom_yml' );

?>
