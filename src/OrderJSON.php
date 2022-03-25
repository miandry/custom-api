<?php
/**
 * Created by PhpStorm.
 * User: miandry
 * Date: 2020/7/30
 * Time: 1:35 PM
 */

namespace Drupal\server_json;


class OrderJSON extends ProductJSON
{
    public $logger;


    public function __construct()
    {
        $this->logger = \Drupal::logger('server_json');
    }
     // import orders from json
    function importOrder($order)
    {
        $title = 'comapp-' . $order['id'];
        $is_exist = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['title' => $title, 'type' => 'commande']);
        $user  = user_load_by_name($order['user']['name']);

        if(!is_object($user)){
            $user = $this->createUserEntity($order['user']);
        }
        if (empty($is_exist) && is_object($user)) {

            $fields['title'] = 'comapp-' . $order['id'];
            $fields['field_client'] = $user->id();
            $fields['field_status_commande'] = 470;

            if ($user->field_adresse) {
                $para = $user->field_adresse->entity;
                if(is_object($para)){
                 $para_new = $para->createDuplicate();
                 $fields['field_adress'] = $para_new;
                }
            }
            foreach ($order['orders'] as $data) {
                $id = $data['cart']['product']['id'];
                $produit = \Drupal::service('entity_parser.manager')->node_parser($id);
                //$prixs = array_values($produit['field_autre_prix']);
                $prix_achat  = 0 ; // ($prixs[0] && $prixs[0]['field_total']) ? $prixs[0]['field_total'] : 0 ;
                $item = [
                    'field_prix_achat' =>($prix_achat)? $prix_achat : 0 ,
                    'field_prix_vente' => $data['cart']['price'],
                    'field_produit' => $data['cart']['product']['id'],
                    'field_quantite' => $data['cart']['quantity'],
                    'field_sku' => $produit['field_sku'],
                    'field_status_produit' => 88,
                    'media' => $produit['medias'][0]['mid']
                ];
                $attr_string ='';

                foreach ($data['cart']['attributeList'] as $key => $attr) {
                    $attr_string = $attr_string . $key . ":" . $attr['value'] . "/";
                }

                $item['field_choix_attribute'] = $attr_string ;
                $fields['field_produits'][] = $item;
            }
            $cart = \Drupal::service('crud')->save('node', 'commande', $fields);
            if (is_object($cart)) {
                return true;
            } else {
                return false;
            }
        }else{
            $this->logger->error("user not exist");
        }
        return false;
    }


    //$id = 'sgalzj0qiifkdqp8twl';
    function isExistOrderJSON($comm){
        if(is_object($comm)){
           $title = $comm->label() ;
        }else{
            $title = $comm ;
        }
        $id = str_replace('comapp-','',$title);
        $param = "filter=[?id==%27".$id."%27]" ;
        $json = $this->getItems('orders',$param);
         if(!empty($json)){
             return reset(json_decode($json, TRUE));
         }else{
             return false ;
         }
    }

    function orderUpdateJSON($commande){
        $result = $this->isExistOrderJSON($commande);
        if($result){
            $result_api = $this->itemApiOrder($commande,$result);
            return $this->updateItem('orders',$result_api);
        }else{
            \Drupal::logger('server_json')->error("commande not exist in app");
        }



    }

    function itemApiOrder($node,$result=[]){
        $parser_node_json = \Drupal::service('entity_parser.manager') ;
        $commande_array = $parser_node_json->node_parser($node);
        $status = $commande_array['field_status_commande']['tid'];
        $result['status'] = $status ;
        $result['nid'] = $node->id() ;
        $client = $node->field_client->entity ;
        $result['user'] = $this->itemApiUser($client);
        foreach (array_values($commande_array['field_produits']) as $key => $cart){
            $node_produit = $cart['field_produit']['node'];
            $produit = $this->productItem($node_produit);

            $result['orders'][$key]['cart']['price'] = $cart['field_prix_vente'];
            $result['orders'][$key]['cart']['quantity'] =  $cart['field_quantite'];
            $result['orders'][$key]['cart']['user'] = $node->field_client->entity->getUserName();
            $result['orders'][$key]['cart']['product'] = $produit ;
            $attr_new = $cart['field_choix_attribute']  ;
            $attr_news = explode('/',$attr_new);
            foreach ($attr_news as $key_attr => $attr) {
                $attr_item = explode(':',$attr);
                if($attr_item[0] && $attr_item[1]){

                    $result['orders'][$key]['cart']['attributeList'][$attr_item[0]] = [
                        'value' => $attr_item[1],
                    ] ;
                    if($attr_item[0] == 'Couleur'){
                        $media = $cart['media']['object'] ;
                        $image = $parser_node_json->image_file($media, 'field_media_image', '220x240');
                        $result['orders'][$key]['cart']['attributeList'][$attr_item[0]]['image'] = $image[0]['image'];
                    }

                }
            }


        }
        return $result ;
        //return $this->updateItem('orders',$result);
    }
    public function sumTotalOrder($node)

    {
        if($node->field_produits){
        $products = $node->field_produits->referencedEntities();
            $total_achat = 0;
            $total_vente = 0;
            if (!empty($products)) {
                foreach ($products as $key => $paragraph) {
                    if (is_object($paragraph)) {
                        $paragraph_array =  $this->parser($paragraph);
                        if (!empty($paragraph_array) && isset($paragraph_array["field_quantite"])) {
                            if (isset($paragraph_array["field_prix_achat"])) {
                                $total_achat = $total_achat + floatval($paragraph_array["field_prix_achat"]) * floatval($paragraph_array["field_quantite"]);
                            }
                            if (isset($paragraph_array["field_prix_vente"])) {
                                $total_vente = $total_vente + floatval($paragraph_array["field_prix_vente"]) * floatval($paragraph_array["field_quantite"]);
                            }

                        }
                    } else {
                        drupal_get_messages('error', "Paragraph Null");
                    }

                }

            }
            return array(
                "total_achat" => $total_achat,
                "total_vente" => $total_vente
            );
        }
    }

    public function numero_products($node)
    {
        $is_ready = $this->is_field_ready($node,"field_produits");
        if($is_ready){
            $products = $node->field_produits->referencedEntities();
            $total_achat = 0;
            $total_vente = 0;
            if (!empty($products)) {
                foreach ($products as $key => $paragraph) {
                    if (is_object($paragraph)) {
                        $paragraph->set("field_numero_produit", [
                            'value' => $node->id() . "-" . $key
                        ]);
                        $paragraph->save();
                    } else {
                        drupal_get_messages('error', "Paragraph Null");
                    }

                }

            }
            return array(
                "total_achat" => $total_achat,
                "total_vente" => $total_vente
            );
        }
    }
}