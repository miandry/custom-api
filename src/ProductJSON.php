<?php
/**
 * Created by PhpStorm.
 * User: miandry
 * Date: 2020/7/30
 * Time: 1:35 PM
 */

namespace Drupal\server_json;


class ProductJSON extends ServerJSON
{
    public $logger;


    public function __construct()
    {
        $this->logger = \Drupal::logger('server_json');
    }

    function processProduct($data)
    {
        $news = $this->productApiFormatter($data);
        foreach ($news as $key => $item) {
            $response[] = $this->updateItem('products', $item);
        }
        return $response;

    }

    function productApiFormatter($nodes)
    {
        foreach ($nodes as $key => $node) {
            $results[$node->id()] = $this->productItem($node);
        }
        return $results;
    }

    public function productItem($node)
    {
        $parser_node_json = \Drupal::service('entity_parser.manager');
        $nid = $node->id();
        $host = \Drupal::request()->getSchemeAndHttpHost();
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $nid);
        $results = [];
        $prices = $node->field_autre_prix->referencedEntities();
        $attributes = $node->field_pr->referencedEntities();
        $medias = $node->medias->referencedEntities();
        $prices_list = [];
        $price_default = 0;
        if (!empty($prices)) {
            foreach ($prices as $key => $price) {
                $prices_new = [];
                $term = $price->field_prixnom->entity;
                if (is_object($term)) {
                    $prices_new['name'] = $term->label();
                    $prices_new['id'] = $term->id();
                    $prices_new['price'] = $price->field_prix_vente->value;
                    if ($price->media && $price->media->entity) {
                        $media = $price->media->entity;
                        $image = $parser_node_json->image_file($media, 'field_media_image', '220x240');
                        $prices_new['image'] = $image[0]['image'];
                    }
                    $prices_list[] = $prices_new;
                }
            }
            $price_default = $prices_list[0]['price'];
        }
        $images = [];
        $imagesOrigin = [];
        if (!empty($medias)) {
            foreach ($medias as $key => $media) {
                $image = $parser_node_json->image_file($media, 'field_media_image', '220x240');
                $images[] = $image[0]['image'];
                $imagesOrigin[] = $image[0]['url'];
            }
        }
        $attr_new = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                $media_attr = $attr->medias->referencedEntities();
                $term_attr = $attr->field_typ->entity;
                $values_attr = explode('/', $attr->field_text_long->value);
                $values = [];
                foreach ($values_attr as $key => $tt) {
                    $item_attr = ['value' => $tt];
                    if ($media_attr[$key]) {
                        $image = $parser_node_json->image_file($media_attr[$key], 'field_media_image', '220x240');
                        $item_attr['image'] = $image['image'];
                    }
                    $values[] = $item_attr;
                }
                if (is_object($term_attr)) {
                    $attr_new[] = [
                        'type' => $term_attr->label(),
                        'id' => $term_attr->id(),
                        'values' => $values
                    ];
                }
            }
        }
        $category = $node->field_catalogue->entity;
        $body = $this->imageFullUrl($node, 'body');
        if (is_object($category)) {
            $promo = ($node->promote && $node->promote->value == 1) ? "true" : "false";
            $dispo = "false";
            if ($node->field_etat_produit &&
                $node->field_etat_produit->entity &&
                $node->field_etat_produit->entity->id() == 500
            ) {
                $dispo = "true";
            }
            $results = [
                'title' => $node->label(),
                'id' => $node->id(),
                'promo' => $promo,
                'dispo' => $dispo,
                'body' => $body,
                'description' => $node->body->summary,
                'price' => $price_default,
                'price_promo' => ($node->field_promote) ? $node->field_promote->value : "0",
                'priceList' => $prices_list,
                'images' => $images,
                'imagesOrigin' => $imagesOrigin,
                'attributes' => $attr_new,
                'category' => ['value' => $category->label(), 'id' => $category->id()],
                'url' => $host . $alias
            ];
        }
        return $results;
    }

    function insertAPIProduit($node)
    {
        $module_name = 'mz_crud';
        $db = \Drupal::database();
        $query = $db->select('node_field_data', 'n');
        $query->fields('n', ['nid']);
        $query->condition('n.type', 'product', '=');
        $query->condition('n.title', $node['title'], '=');
        $resultat = $query->execute()->fetchAll();
        $is_crud = \Drupal::moduleHandler()->moduleExists($module_name);
        if ($is_crud && empty($resultat)) {
            $fields = [
                'title' => $node['title'],
                'body' => $node['body'][0]['value'],
                'term' => $node['field_catalogue']['title'],
                'sku' => $node['field_sku']
            ];
            //prices
            if(!empty($node['field_autre_prix'])){
                $i = 0 ;
                foreach ($node['field_autre_prix'] as $price) {
                    if($i == 0){
                        $fields['price'] = $price['field_prix_vente']/5 ;   
                        $fields['price_achat'] = floatval($price['field_total_achat']/5) ;   
                        $i++ ;
                    }
                    $fields['prices'][] = [
                        'term' => $price['field_prixnom']['title'],
                        'price' => $price['field_prix_vente']/5
                    ];
                }
            }
            // images
            if(!empty($node['medias'])){
                foreach ($node['medias'] as $media) {
                    $fields['medias'][] = $media['image']['url'];
                }
            }
            //attributes
            if(!empty($node['field_pr'])){
                foreach ($node['field_pr'] as $attr) {
                    $attr_images = [];
                    if(isset($attr['medias'])){
                        foreach ($attr['medias'] as $media_attr) {
                            $attr_images[] = $media_attr['image']['url'];
                        }
                    }

                    $fields['attribute'][] = [
                        'attribute_value' => isset($attr['field_text_long'])?explode('/',$attr['field_text_long']):'',
                        'term' => $attr['field_typ']['title'],
                        'medias' => $attr_images
                    ];
                }
            }
            $prod = \Drupal::service('crud')->save('node', 'product', $fields);
            if (is_object($prod)) {
                \Drupal::logger('server_json')->error('Success to saved product id='.$prod->id());
                return $prod->id();
            } else {
                \Drupal::logger('server_json')->error('Failed to saved product');
                return null;
            }
        } else {
            \Drupal::logger('server_json')->error('Module mz_crud not enabled');
            return null;
        }

    }

    function updateProduct($node)
    {
        $json = $this->productItem($node);
        return $this->updateItem('products', $json);
    }

    function migrateProduct($nodes)
    {

    }

    function productUpdateJSON($node)
    {
        if ($node->status) {
            $status = $node->status->value;
            if ($status == 1) {
                $node_json = $this->productItem($node);
                $result = $this->updateItem('products', $node_json);
                if ($result && $result['status']) {
                    $msg = ' Pushed to app Successfull ';
                    \Drupal::messenger()->addMessage($msg);
                }
            }
            if ($status == 0) {
                $this->deleteProduct($node);
            }
        }

    }

    public function deleteProduct($product)
    {

        $product_json = $this->productItem($product);
        return $this->deleteItem('products', $product_json);

    }

}