<?php

namespace Drupal\server_json\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DefaultController.
 */
class ServerJSONController extends ControllerBase {

    /**
     * Revendeur.
     *
     * @return string
     *   Return Hello string.
     */
    public function orders() {
        $param = '';
      //  $path = '/Volumes/ARCHIVE/projects/miandy_project/nodejs/api/api/orders.json';
        $json = \Drupal::service('order_json')->getItems('orders',$param);
        $url = \Drupal::service('order_json')->URL();
        $results = \Drupal\Component\Serialization\Json::decode($json);
//     //   $results  = \Drupal::service('order_json')->getFileContent($path) ;
//    //    kint($results);
//        $header = [
//            'id' => t('Date'),
//            'username' => t('username'),
//            'email' => t('Email'),
//        ];
//        // Initialize an empty array
//         $output = array();
//        foreach ($results as $key => $result) {
//            $user = $result['user'];
//            $orders = $result['orders'];
////            if ($result->uid != 0 && $result->uid != 1) {
//                $output[$result['id']] = [
//                    'id' => $result['createdAt'],     // 'userid' was the key used in the header
//                    'username' => $user['name'], // 'Username' was the key used in the header
//                    'mail' =>  $user['mail'],    // 'email' was the key used in the header
//                ];
////            }
//        }
//        $form['table'] = array(
//            '#type' => 'table',
//            '#header' => $header,
//            '#rows' => $output,
//            '#empty' => $this->t('No variables found')
//        );
//
//        return $form ;

        $build = [
            '#theme' => 'server_json'
        ];
        $items['results'] = $results;
        $items['url'] = $url;
        $build["#items"]= $items ;
        return $build;

    }

    public function jsonFiles(){
        $path = '/Volumes/ARCHIVE/projects/miandy_project/angular/ionic/mizara/src/assets/data';
        $results  = \Drupal::service('filereader')->readDirectory($path) ;
        $header = [
            'number' =>  t('Numero'),
            'name' => t('File name'),
            'path' => t('path'),
            'edit' => array('data' => $this->t('Operations'))
        ];
        $output = [];
        $destination = $this->getDestinationArray();
        foreach ($results as $key => $result) {
                $operations['edit'] = array(
                    'title' => $this->t('Edit'),
                    'url' => Url::fromRoute('devel.config_edit', array('config_name' => 'system')),
                    'query' => $destination
                );
                $output[] = [
                    'id' => $key ,
                    'name' => 'File',
                    'path' =>  $result,
                    'operation' => array('data' => array('#type' => 'operations', '#links' => $operations)),
                ];

        }
        $form['table'] = array(
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $output,
            '#empty' => $this->t('No variables found')
        );
//
        return $form ;

    }

    public function jsonRenderAPI($id){
        $results = ["api" => $id];
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
        $parser_node_json = \Drupal::service('render_json');
        $results = $parser_node_json->renderPageJSON($node);
        return new JsonResponse($results);
    }

}
