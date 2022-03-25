<?php
/**
 * Created by PhpStorm.
 * User: miandry
 * Date: 2020/7/30
 * Time: 1:35 PM
 */

namespace Drupal\server_json;

use Drupal\Component\Utility\Html;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;


class ServerJSON extends RenderJSONManager {
    public $logger;

    public function __construct()
    {
        $this->logger = \Drupal::logger('server_json');
    }

    function generatePage($items,$node){
        $config = \Drupal::config("filereader.source");
        $path_file =  $this->getDefaultPathPage();
        $content_file = Json::encode($items);
        $alias =  \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node->id());
        $filename = $this->getPageName($alias);
        $directory = $this->getPageDirectory($alias);
        $directory = trim($path_file).$directory ;
        $status = \Drupal::service('filereader')->generateFileForce($directory,$filename.'.json' , $content_file);
        if ($status) {
            \Drupal::messenger()->addMessage('File creation was successfully');
        } else {
            \Drupal::messenger()->addError('Failed to create the file');
        }
    }
    function getDefaultPathPage(){
        $config = \Drupal::config("filereader.source");
        $path_file =  ($config && $config->get('source'))?$config->get('source'): '' ;
        $output_array = explode('|',$path_file) ;
        if(!empty($output_array)){
            return $output_array[0];
        }else{
            \Drupal::messenger()->addError('Failed to get source path ');
            return $path_file;
        }
    }
    function getPageName($alias){
        $alias_array = explode('/',$alias);
        return $alias_array[sizeof($alias_array)-1];
    }

    function getPageDirectory($alias){
        $filename = $this->getPageName($alias);
        return trim(str_replace('/'.$filename,'',$alias));
    }
    function imageFullUrl($entity, $field)
    {
        $default = "data:image/png;base64,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b24+CiAgIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+CvBn2aEAAAAcaURPVAAAAAIAAAAAAAAAowAAACgAAACjAAAAowAABz6YNsWAAAAHCklEQVR4AezZQWojaRCEUdf9j6WDyWAQQggkFAs5Kniz6elpF/Pny4Rv0cflcrn++IcAAQIECIwJHAI3tlHjECBAgMCfgMA5BAIECBCYFBC4ybUaigABAgQEzg0QIECAwKSAwE2u1VAECBAgIHBugAABAgQmBQRucq2GIkCAAAGBcwMECBAgMCkgcJNrNRQBAgQICJwbIECAAIFJAYGbXKuhCBAgQEDg3AABAgQITAoI3ORaDUWAAAECAucGCBAgQGBSQOAm12ooAgQIEBA4N0CAAAECkwICN7lWQxEgQICAwLkBAgQIEJgUELjJtRqKAAECBATODRAgQIDApIDATa7VUAQIECAgcG6AAAECBCYFBG5yrYYiQIAAAYFzAwQIECAwKSBwk2s1FAECBAgInBsgQIAAgUkBgZtcq6EIECBAQODcAAECBAhMCgjc5FoNRYAAAQIC5wYIECBAYFJA4CbXaigCBAgQEDg3QIAAAQKTAgI3uVZDESBAgIDAuQECBAgQmBQQuMm1GooAAQIEBM4NECBAgMCkgMBNrtVQBAgQICBwboAAAQIEJgUEbnKthiJAgAABgXMDBAgQIDApIHCTazUUAQIECAicGyBAgACBSQGBm1yroQgQIEBA4NwAAQIECEwKCNzkWg1FgAABAgLnBggQIEBgUkDgJtdqKAIECBAQODdAgAABApMCAje5VkMRIECAgMC5AQIECBCYFBC4ybUaigABAgQEzg0QIECAwKSAwE2u1VAECBAgIHBugAABAgQmBQRucq2GIkCAAAGBcwMECBAgMCkgcJNrNRQBAgQICJwbIECAAIFJAYGbXKuhCBAgQEDg3AABAgQITAoI3ORaDUWAAAECAucGCBAgQGBSQOAm12ooAgQIEBA4N0CAAAECkwICN7lWQxEgQICAwLkBAgQIEJgUELjJtRqKAAECBATODRAgQIDApIDATa7VUAQIECAgcG6AAAECBCYFBG5yrYYiQIAAAYFzAwQIECAwKSBwk2s1FAECBAgInBsgQIAAgUkBgZtcq6EIECBAQODcAAECBAhMCgjc5FoNRYAAAQIC5wYIECBAYFJA4CbXaigCBAgQEDg3QIAAAQKTAgI3uVZDESBAgIDAuQECBAgQmBQQuMm1GooAAQIEBM4NECBAgMCkgMBNrtVQBAgQICBwboAAAQIEJgUEbnKthiJAgAABgXMDBAgQIDApIHCTazUUAQIECAicGyBAgACBSQGBm1yroQgQIEBA4NwAAQIECEwKCNzkWg1FgAABAgLnBggQIEBgUkDgPlzr9Xr98Iv7jx/Hcf/NF//tjG/+Io//FQECowIC9+FizxiLM775w7X4cQIECDwJCNwTyev/cMZYnPHNr7fgTwkQIPBeQODeGz38xBljccY3P6D7DQECBAIBgQvQkmD819+/3cY745tvb/crAQIEEgGBS9R8Q4AAAQL1AgJXvyIPJECAAIFEQOASNd8QIECAQL2AwNWvyAMJECBAIBEQuETNNwQIECBQLyBw9SvyQAIECBBIBAQuUfMNAQIECNQLCFz9ijyQAAECBBIBgUvUfEOAAAEC9QICV78iDyRAgACBREDgEjXfECBAgEC9gMDVr8gDCRAgQCARELhEzTcECBAgUC8gcPUr8kACBAgQSAQELlHzDQECBAjUCwhc/Yo8kAABAgQSAYFL1HxDgAABAvUCAle/Ig8kQIAAgURA4BI13xAgQIBAvYDA1a/IAwkQIEAgERC4RM03BAgQIFAvIHD1K/JAAgQIEEgEBC5R8w0BAgQI1AsIXP2KPJAAAQIEEgGBS9R8Q4AAAQL1AgJXvyIPJECAAIFEQOASNd8QIECAQL2AwNWvyAMJECBAIBEQuETNNwQIECBQLyBw9SvyQAIECBBIBAQuUfMNAQIECNQLCFz9ijyQAAECBBIBgUvUfEOAAAEC9QICV78iDyRAgACBREDgEjXfECBAgEC9gMDVr8gDCRAgQCARELhEzTcECBAgUC8gcPUr8kACBAgQSAQELlHzDQECBAjUCwhc/Yo8kAABAgQSAYFL1HxDgAABAvUCAle/Ig8kQIAAgURA4BI13xAgQIBAvYDA1a/IAwkQIEAgERC4RM03BAgQIFAvIHD1K/JAAgQIEEgEBC5R8w0BAgQI1AsIXP2KPJAAAQIEEgGBS9R8Q4AAAQL1AgJXvyIPJECAAIFEQOASNd8QIECAQL2AwNWvyAMJECBAIBEQuETNNwQIECBQLyBw9SvyQAIECBBIBAQuUfMNAQIECNQLCFz9ijyQAAECBBIBgUvUfEOAAAEC9QICV78iDyRAgACBREDgEjXfECBAgEC9gMDVr8gDCRAgQCARELhEzTcECBAgUC8gcPUr8kACBAgQSAQELlHzDQECBAjUC/wCAAD//7vef08AAAY1SURBVO3VAQkAMAwDwdW/rArbYDKeq4NeApndvccRIECAAIGYwBi4WKLeIUCAAIEvYOAUgQABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAgYuGaunCBAgQMDA6QABAgQIJAUMXDJWTxEgQICAgdMBAgQIEEgKGLhkrJ4iQIAAAQOnAwQIECCQFDBwyVg9RYAAAQIGTgcIECBAIClg4JKxeooAAQIEDJwOECBAgEBSwMAlY/UUAQIECBg4HSBAgACBpICBS8bqKQIECBAwcDpAgAABAkkBA5eM1VMECBAgYOB0gAABAgSSAg8CIE0ziAqJLgAAAABJRU5ErkJggg==";
        $bool = \Drupal::service('drupal.helper')->helper->is_field_ready($entity, $field);
        if ($bool) {
            $body = $entity->{$field}->value;
            global $base_url;
            $dom = Html::load($body);
            foreach ($dom->getElementsByTagName('img') as $img) {
                $src = $img->getAttribute('src');
                //    $img->setAttribute("class", "b-lazy");
                $img->setAttribute("src", $base_url . $src);
                //   $img->removeAttribute('src');
            }
            $body = $dom->saveHTML();
            $json = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body|meta))[^>]*>\s*~i', '', $body);
            return $json;
        }

        return [];

    }

    function updateSettings()
    {
        //     $settings = $this->getItems('settings');
        $settings = $this->buildSettings();
        $response = $this->updateItem('settings',$settings);
        if($response['status']){
            $msg  = 'Setting json updated Successfull ';
            \Drupal::messenger()->addMessage($msg);
        }
    }

    function buildSettings()
    {
        $site_name = \Drupal::config('system.site')->get('name');
        $settings_json = ['id' => '0' ,'site_name' => $site_name];

        //@todo make it dynamic
        $settings_json['info'] = [
            'title' => 'My shop',
            'content' => 'Welcome to my shop'
        ];
        $category = $this->generateCategory();
        $settings_json['category'] = $category;

        $pay_status = $this->buildTerms('pay_status');
        $settings_json['pay_status'] = $pay_status;

        $prix_type = $this->buildTerms('prix_type');
        $settings_json['prix_type'] = $prix_type;


        $status_commande_process = $this->buildTerms('status_commande_process');
        $settings_json['status_commande_process'] = $status_commande_process;

        $payement = $this->buildTerms('payement');
        $settings_json['payement'] = $payement;

        return $settings_json;
    }
    function buildTerms($vid){
         $helper = \Drupal::service('drupal.helper');
         $terms = $helper->helper->taxonomy_load_multi_by_vid($vid);
         $items =[];
         foreach ($terms as $key => $term) {
           $items[$term['tid']] = $term['name'] ;
         }
         return $items ;
    }
    function colsFromArray(array $array, $keys)
    {
        if (!is_array($keys)) $keys = [$keys];
        $filter = function($k) use ($keys){
            return in_array($k,$keys);
        };
        return array_map(function ($el) use ($keys,$filter) {
            return array_filter($el, $filter, ARRAY_FILTER_USE_KEY );
        }, $array);
    }
    function writeJSONFile($news)
    {
        $ids = array_keys($news);
        $config = \Drupal::config('server_json.settings');
        $path_file = $config->get('path_product');
        $status = false;
        if ($path_file) {
            $results = $this->getFileContent($path_file);
            foreach ($news as $key => $item) {
                $response = \Drupal::service('server_json')->updateItem('products', $item);

                if (in_array($item['id'], $ids)) {
                    $results[$key] = $news[$item['id']];
                    unset($news[$item['id']]);
                }
            }
            $results = array_merge(array_values($news), $results);
            $status = $this->udpateFileContent($results, $path_file);
        }
        return $status;
    }

    function getFileContent($path_file)
    {
        $file = $path_file;

        if (file_exists($file)) {
            $json = file_get_contents($file, FILE_USE_INCLUDE_PATH);
            return json_decode($json, TRUE);
        } else {
            $this->logger->error('File  not find exist : ' . $file);
            return FALSE;
        }

    }

    function updateItem($entity_name, $item)
    {   $url = $this->URL();
        $url_full = $url . '/api/' . $entity_name ;
        $status = $this->urlExist($url_full);
        if($status){
          $serialized_entity = json_encode($item);
            $response = \Drupal::httpClient()
                ->put($url . '/api/' . $entity_name, [
                    'verify' => true,
                    'body' => $serialized_entity,
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                ]);
        return $response->getBody()->getContents();
        }else{
            return false ;
        }

    }
    function urlExist($url) {
        $file_headers = @get_headers($url);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists ;
    }
    function getItems($entity_name, $param = '')
    {
        $url = $this->URL();
        $url_path = $url . '/api/' . $entity_name .'?'. $param;
        $status = $this->urlExist($url_path);
        if($status){
        $response = \Drupal::httpClient()
            ->get($url_path);
        return $response->getBody()->getContents();
        }else{
            return false ;
        }
    }

    function deleteItem($entity_name, $item)
    {
        $url = $this->URL();
        $url_path = $url . '/api/' . $entity_name ;
        $status = $this->urlExist($url_path);
        if($status){
        $serialized_entity = json_encode($item);
        $response = \Drupal::httpClient()
            ->delete($url . '/api/' . $entity_name, [
                'verify' => true,
                'body' => $serialized_entity,
                'headers' => [
                    'Content-type' => 'application/json',
                ],
            ]);
        return $response->getBody()->getContents();
        }else{
            return false ;
        }
    }

    function createItem($entity_name, $item)
    {
        $serialized_entity = json_encode($item);
        $url = $this->URL();
        $url_path = $url . '/api/' . $entity_name ;
        $status = $this->urlExist($url_path);
        if($status){
            $response = \Drupal::httpClient()
                ->post($url . '/api/' . $entity_name, [
                    'verify' => true,
                    'body' => $serialized_entity,
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                ]);
            return $response->getBody()->getContents();
        }else{
            return false ;
        }
    }

    function udpateFileContent($content, $path_file)
    {
        $file = $path_file;
        @chmod($file, 0777);
        if (file_put_contents($file, json_encode($content, TRUE)) === FALSE) {
            $this->logger->error('Failed to write file ' . $file);
            return FALSE;
        }
        // if (!@chmod($file, 0777)) {
        //  $this->logger->error('Failed to change permission file ' . $file);
        //  return FALSE;
        // }
        return TRUE;
    }

    public function URL()
    {
        $config = \Drupal::config('server_json.settings');
        if ($config->get('path_url')) {
            return $config->get('path_url');
        } else {
            $this->logger->error('Url server json not find update /admin/config/deploy ');
            return null;
        }
    }

    function generateCategory()
    {
        $service = \Drupal::service('drupal.helper');
        $parents = $service->helper->taxonomy_first_level_by_vid('catalogue');
        $categories = [];

        foreach ($parents as $parent) {

            $term = \Drupal::service('entity_parser.manager')->parser($parent['object']);
            $category = $parent['tid'];
            $child = $service->helper->taxonomy_get_children($parent['tid']);
            foreach ($child as $item) {
                $category = $category . "," . $item;
            }
            $categories[] = [
                "value" => $category,
                "label" => $parent['name'],
                "image" => ($term['image']) ? $term['image']['image']['url'] : ''
            ];

        }
        return $categories;
    }
    function isUserNameExist($name){
        $query = \Drupal::entityQuery('user')
            ->condition('name', $name);
        $query->range(0, 1);
        $result = $query->execute();
        if(!empty($result)){
            return true;
        }
        return false ;
    }
    function createUserEntity($data){

        if (isset($data['name']) && isset($data['phone'])) {
            $status = $this->isUserNameExist($data['name']);
            if (!$status) {
                $user = User::create();
                $user->setPassword('00000');
                $user->enforceIsNew();
                if($data['mail']){
                    $user->setEmail($data['mail']);
                }else{
                    $user->setEmail("email@yahoo.fr");
                }
                $user->setUsername($data['name']); //This username must be unique and accept only a-Z,0-9, - _ @ .

                $field_adress = [
                    'field_adress' => $data['adress']['province'] . " - " . $data['adress']['city'] . " - " . $data['adress']['location'],
                    'field_email' => $data['mail'],
                    'field_phone' => $data['phone']
                ];
                $adress = \Drupal::service('crud')->save('paragraph', 'adress', $field_adress);
                $user->set("field_adresse", $adress);
                $user->save();
            }
            return user_load_by_name($data['name']);
        }
        return null ;
    }
    function itemApiUser( $user,$status=true){
            $user_array = \Drupal::service('entity_parser.manager')->user_parser($user);
            $hashed_password = $user->getPassword();
            $json['mail'] = $user_array['mail'];
            $json['name'] = $user->getUserName();
            $json['token'] = \Drupal\Component\Utility\Crypt::hashBase64($hashed_password);
            $json['status'] = $status;
            if($user_array['field_adresse']){
                $adress = array_values($user_array['field_adresse'])[0];
                $adress_array = explode('-',$adress['field_adress']);
                $json['adress']  = [
                    'city' => ($adress_array)?$adress_array[1] : "",
                    'location' => ($adress_array)?$adress_array[2] : "",
                    'province' => ($adress_array)?$adress_array[0] : ""
                ];
                $json['phone'] = ($adress['field_phone'])? $adress['field_phone'] : "" ;
            }
            $json['id'] = $user->id();
            return  $json ;
    }
    function senAPIItem($url, $content)
    {
        $status = $this->urlExist($url);
        if($status){
            $serialized_entity = json_encode($content);
            $response = \Drupal::httpClient()
                ->post($url , [
                    'verify' => true,
                    'timeout' => 1600,
                    'body' => $serialized_entity,
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                ]);
            return $response->getBody()->getContents();
        }else{
            return false ;
        }

    }
    function insertAPIEntity($fields,$entity_name,$bundle)
    {
           $entity = \Drupal::service('crud')->save($entity_name,$bundle, $fields);
            if (is_object($entity)) {
                \Drupal::logger('server_json')->error('Success to saved entity id='.$entity->id());
                return $entity->id();
            } else {
                \Drupal::logger('server_json')->error('Failed to saved entity');
                return null;
            }
    }

}