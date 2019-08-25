<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Controllers;

use Noodlehaus\Config as Config;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Delight\FileUpload\FileUpload as Upload;
use App\Functions\Factory as Fn;
use Delight\FileUpload\Throwable as Throwable;
use PhpZip\ZipFile as Zipper;

class ProcessController extends Controller {
  
  # Header holder
  private $header;
  
  # Destination languages
  private $destination;
  
  # File data
  private $data = [
    'file' => null,
    'fullpath' => '',
  ];
  
  # Msgid and Msgstr reader
  private $result = [
    'msgid' => null,
    'msgstr' => null,
  ];
  
  /**
   * Set header
   */
  public function __construct(){
    $this->header = getallheaders();
  }
  
  /**
   * Start the chain
   * @param  Request  $request  [Slim Request OBJ]
   * @param  Response $response [Slim Response OBJ]
   * @return [void]
   * @usage curl -X POST -l \
      'http://localhost/translaxor' \
      -H 'Connection: keep-alive' \
      -H 'User-Agent: AgentSmith/7.15.2' \
      -H 'Destination-Languages: [EN-RU]' \
      -F file=@/Users/{username}/Desktop/source.po \
      --output /Users/{username}/Desktop/translations.zip \
      && open /Users/{username}/Desktop/translations.zip \
      && code /Users/{username}/Desktop/translations/.
   */
  public function run(Request $request, Response $response){
    
    $process = $this->destination()->upload($response, new Upload, 'file');
    
    # upload fucked up hard
    if($process instanceof Response){
      return $process;
    }
    
    return $process->regexp()->translate('TR')->download();
  }
  
  /**
   * Determine destination languages
   */
  private function destination(){
    
    if(Fn::data()->get($this->header['Destination-Languages'])){
      preg_match_all("/\[([^\]]*)\]/", trim($this->header['Destination-Languages']), $matches);
      
      if(false == Fn::data()->get($matches[1][0])){
        return $response->withJson([
          'success' => false,
          'message' => 'Something wrong with destination language.',
        ]);
      }
      
      $languages = explode('-', $matches[1][0]);
    }
    
    $this->destination = Fn::data()->get($languages, ['EN']);
    
    return $this;
  }

  /**
   * Handle file uploads
   * @param $name name to be looked for in the request
   */
  private function upload(Response $response, Upload $upload, $name){
    
    $upload
    ->withTargetDirectory(ROOT_DIR . '/uploads')
    ->from($name)
    ->withMaximumSizeInMegabytes(2)
    ->withAllowedExtensions(['po']);
   
    try {
      
      $this->data['fullpath'] = ROOT_DIR . '/uploads/' . $upload->save()->getFileNameWithExtension();
      $this->data['file'] = file_get_contents($this->data['fullpath']) . "\n\n";

    }catch (Throwable\InputNotFoundException $e) {
      return $response->withJson([
        'success' => false,
        'message' => 'Input not found',
      ]);
    }catch (Throwable\InvalidFilenameException $e) {
      return $response->withJson([
        'success' => false,
        'message' => 'Invalid filename',
      ]);
    }catch (Throwable\InvalidExtensionException $e) {
      return $response->withJson([
        'success' => false,
        'message' => 'Invalid extension',
      ]);
    }catch (Throwable\FileTooLargeException $e) {
      return $response->withJson([
        'success' => false,
        'message' => 'File too large',
      ]);
    }catch (Throwable\UploadCancelledException $e) {
      return $response->withJson([
        'success' => false,
        'message' => 'Upload cancelled',
      ]);
    }
    
    return $this;
  }
      
  /**
   * Split PO files accordingly
   */
  private function regexp(){
    
    preg_match_all('/(?s)((^msgid "\K(.*?))(^msgstr))/m', $this->data['file'], $this->result['msgid']);
    preg_match_all('/(?s)((^msgstr "\K(.*?))(^\n))/m', $this->data['file'], $this->result['msgstr']);
    
    return $this; 
  }
  
  /**
   * Translate for Mates
   * @param $source source language to translate from
   */
  private function translate($source = 'TR'){

    foreach($this->destination as $language){
      $holder = [];
      
      $counter = 0;
      foreach($this->result['msgid'][0] as $key => $item){
        if($key == 0){
          continue;
        }
        
        $holder[$counter]['key'] = '"' . mb_substr(str_replace("msgstr", '', $item), 0, -1);
        $counter++; 
      }
      
      $counter = 0;
      foreach($this->result['msgstr'][0] as $key => $item){
        if($key == 0){
          continue;
        }
        
        $holder[$counter]['value'] = GoogleTranslate::trans(
          $holder[$counter]['key'],
          (string) $language,
          (string) $source
        );
        
        $counter++;
        
        usleep(500);
      }
      
      $counter = -1;
      $this->data['file'] = preg_replace_callback('/(?s)((^msgstr \K(.*?))(^\n))/m', function($m) use (&$counter, $holder){
        return $holder[$counter++]['value'] . "\n\n";
      }, $this->data['file']);
      
      # extract name + extension
      $fullpath = explode('.', $this->data['fullpath']);
      
      # save new file
      file_put_contents($fullpath[0] . '-' . $language . '.' . $fullpath[1], $this->data['file']);
    }
    
    return $this;
  }
  
  /**
   * Force file download
   */
  private function download(){
    
    $zipper = new Zipper;
    
    foreach($this->destination as $language){
      $fullpath = explode('.', $this->data['fullpath']);
      
      $zipper
      ->addFile($fullpath[0] . '-' . $language . '.' . $fullpath[1], 'translations/' . $language . '.' . $fullpath[1])
      ->saveAsFile(ROOT_DIR . '/uploads/data.zip')
      ->close();
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Disposition: attachment; filename="' . basename(ROOT_DIR . '/uploads/data.zip') . '"');
    header('Content-Length: ' . filesize(ROOT_DIR . '/uploads/data.zip'));
    header('Connection: close');
    flush();
    
    readfile(ROOT_DIR . '/uploads/data.zip');
    unlink(ROOT_DIR . '/uploads/data.zip');
    
    foreach($this->destination as $language){
      $fullpath = explode('.', $this->data['fullpath']);
      
      unlink($this->data['fullpath']);
      unlink($fullpath[0] . '-' . $language . '.' . $fullpath[1]);
    }
  }

}