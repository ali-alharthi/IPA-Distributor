<?php

/**
 * IPA-Distributor
 *
 * IPA Distribution Class (ad-hoc).
 * Creates manifest from ipa files for distribution or read manifests.
 *
 * @package    IPA
 * @author     Ali Alharthi <root@ali.ninja>
 * @copyright  2017-2018 www.ali.ninja
 * @license    MIT
 * @version    1.0
 * @link       https://www.ali.ninja/projects/ipa.txt
 *
 */
namespace aAlharthi\Ninja;

class IPA {

   /**
   * Temp folder for the dirty work
   *
   * @var string
   */
   protected $temp = 'tmp';

   /**
   * destination folder for output
   *
   * @var string
   */
   protected $destination = 'ipaz'; // Output directory

   /**
   * Full URL for the current path 
   *
   * @var string
   */
   protected $url = 'https://www.ali.ninja/ipa/'; // Full url include http/s:// and folder etc, don't be lazy!

   /**
   * Icon size [2x, 3x] 
   *
   * @var string
   */
   protected $appIconSize = "@2x";

   /**
   * IPA path
   *
   * @var string
   */
   public $ipa;

   /**
   * Extracted IPA path
   *
   * @var string
   */
   public $app;

   /**
   * IPA URL
   *
   * @var string
   */
   public $appIPA;

   /**
   * App bundle name
   *
   * @var string
   */
   public $appName;

   /**
   * App bundle identifier
   *
   * @var string
   */
   public $appBundleID;

   /**
   * App bundle version
   *
   * @var string
   */
   public $appVersion;

   /**
   * Manifest
   *
   * @var string
   */
   public $manifest;

   /**
   * Manifest path
   *
   * @var string
   */
   public $manifestPath;

   /**
   * Initialize and load IPA with an automatic processing option.
   *
   * @param string $ipa the IPA file path.
   * @param boolean $auto for automatic IPA process.
   * @access public
   */
   public function __construct($ipa=NULL, $auto = FALSE){

      /**
      * Check if IPA was set, if so load it
      */
      !empty($ipa)?$this->ipa=$ipa:$this->ipa=null;

      /**
      * Check if auto was set, if so run as follow:
      * 1. Extarct IPA (with both options set to true).
      * 3. Generate a manifest.
      * 4. Delete all the dirty work.
      */
      if ($auto){

         $this->eIPA(TRUE, TRUE);
         $this->icon($this->read());
         $this->generate();
         $this->clean();

      }

   }

   /**
   * Extract IPA files.
   *
   * @param boolean $movetodestination to move IPA to destination.
   * @param boolean $includeappicon to make a copy of app icon to destination.
   * @return string extarcted .app path.
   * @access public
   */
   public function eIPA($movetodestination = TRUE, $includeappicon = TRUE){

      /**
      * Check if IPA exist
      */
      if (!is_file($this->ipa) or empty($this->ipa))
         die("ipa file not found.");

      /**
      * Check if IPA is readable
      */
      if (!is_readable($this->ipa))
         die("can not read ipa file.");

      /**
      * Check if the temp folder exist, if not create it
      */
      if (!is_dir($this->temp))
         mkdir($this->temp) or die("unable to create a folder.");

      $zip = new \ZipArchive;
      /**
      * Check if IPA unzipped correctly
      */
      if ($zip->open($this->ipa) === TRUE){

         $zip->extractTo('tmp');
         $zip->close();

         if (glob($this->temp."/Payload/*.app")){

            $this->app = glob($this->temp."/Payload/*.app");
            $this->app = $this->app[0];

            if (!is_dir($this->app)){
               // $this->clean(); // clear temp destination
               die("ipa not recognized.");
            }

            if (!file_exists($this->app."/Info.plist")){
               // $this->clean(); // clear temp destination
               die("Info.plist not found.");
            }

         } else {

            $this->clean(); // clear temp 
            die("ipa not recognized."); // maybe not supported yet

         }

      } else {

         die("ipa not recognized.");

      }

      /**
      * Read app info and apply user settings
      */
      $this->read();      
      if ($movetodestination)
         $this->move();
      if ($includeappicon)
         $this->icon($this->read());
      
      return $this->app;

   }

   /**
   * Read app information (info.plist).
   *
   * @param string $manifest to read using readManifest() function.
   * @return array with app info.
   * @access public
   */
   public function read($manifest = NULL){
      
      /**
      * Check if manifest is set, if so send it to readManifest function
      */
      if (!empty($manifest))
         return $this->readManifest($manifest);

      /**
      * Check if manifest exist
      */
      if (!file_exists($this->app."/Info.plist"))
         die("Info.plist not found.");

      /**
      * Read plist file using CFPropertyList and check if its empty
      */
      $list[] = null;
      require_once('CFPropertyList/CFPropertyList.php');
      $plist = new \CFPropertyList\CFPropertyList( $this->app . '/Info.plist' );
      $plistArray = $plist->toArray();
      if (empty($plistArray))
         die("Info.plist is empty.");

      /**
      * Load app info
      */
      $this->appIPA = $this->url.$this->ipa;
      $this->appName = $plistArray['CFBundleDisplayName'];
      $this->appBundleID = $plistArray['CFBundleIdentifier'];
      $this->appVersion = $plistArray['CFBundleVersion'];

      $list['appIPA'] = $this->appIPA;
      $list['appName'] = $this->appName;
      $list['appBundleID'] = $this->appBundleID;
      $list['appVersion'] = $this->appVersion;
      
      return $list;
   }

   /**
   * Extraxt information from a manifest.
   *
   * @param string $manifest to manifest path.
   * @return array with app info.
   * @access protected
   */
   protected function readManifest($manifest){
      
      /**
      * Check if manifest exist
      */
      if (!file_exists($manifest))
         die("manifest not found.");

      /**
      * Check if manifest is readable
      */
      if (!is_readable($manifest))
         die("can not read manifest.");

      /**
      * Load the manifest
      */
      $this->manifest = file_get_contents($manifest);
      $this->manifestPath = $manifest;

      /**
      * Read plist file using CFPropertyList and check if its empty
      */
      $list[] = null;
      require_once('CFPropertyList/CFPropertyList.php');
      $plist = new \CFPropertyList\CFPropertyList( $this->manifestPath );
      $plistArray = $plist->toArray();
      if (empty($plistArray))
         die("manifest is empty.");

      /**
      * Load app info
      */
      $this->appIPA = $plistArray['items']['0']['assets']['0']['url'];
      $this->appName = $plistArray['items']['0']['metadata']['title'];
      $this->appBundleID = $plistArray['items']['0']['metadata']['bundle-identifier'];
      $this->appVersion = $plistArray['items']['0']['metadata']['bundle-version'];

      $list['appIPA'] = $this->appIPA;
      $list['appName'] = $this->appName;
      $list['appBundleID'] = $this->appBundleID;
      $list['appVersion'] = $this->appVersion;
      
      return $list;
   }

   /**
   * Copy app icon to destination.
   *
   * @param array $info to app information.
   * @return copies icon path.
   * @access protected
   */
   protected function icon($info){


      $icon = str_replace(' ', '', strtolower(basename($this->ipa, ".ipa"))).'.png';

      /**
      * Check if destination exist, if not create it
      */
      if (!is_dir($this->destination))
         mkdir($this->destination) or die("unable to create a folder.");

      /**
      * Get the app icon with the size 60x60 if we could find it
      */
      foreach (glob($this->app.'/*'.$this->appIconSize.'.png') as $file) {
         if (strpos($file, '60') === false)
            '';
         else
            $appIcon = $file;

      }
      empty($appIcon)?die("no icon found."):copy($appIcon, $this->destination.'/'.$icon);

      return $this->destination.'/'.$icon;
   }

   /**
   * Generate a manifest.
   *
   * @return manifest path.
   * @access public
   */
   public function generate(){

      /**
      * Check if information is missing
      */
      if (empty($this->ipa) or empty($this->bundleID) or empty($this->appVersion) or empty($this->appName))
         empty($this->read())?die("missing information."):'';

      $this->manifest = '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
      <plist version="1.0">
      <dict>
         <key>items</key>
         <array>
            <dict>
               <key>assets</key>
               <array>
                  <dict>
                     <key>kind</key>
                     <string>software-package</string>
                     <key>url</key>
                     <string>'.$this->url.'/'.$this->destination.'/'.$this->ipa.'</string>
                  </dict>
               </array>
               <key>metadata</key>
               <dict>
                  <key>bundle-identifier</key>
                  <string>'.$this->appBundleID.'</string>
                  <key>bundle-version</key>
                  <string>'.$this->appVersion.'</string>
                  <key>kind</key>
                  <string>software</string>
                  <key>title</key>
                  <string>'.$this->appName.'</string>
               </dict>
            </dict>
         </array>
      </dict>
      </plist>';

      /**
      * Use the app name to create a manifest name
      */
      $this->manifestPath = $this->destination.'/'.str_replace(' ', '', strtolower(basename($this->ipa, ".ipa"))).".plist";

      /**
      * Write the manifest to the created name
      */
      $file = fopen($this->manifestPath, "w") or die("unable to create a new file.");
      fwrite($file, $this->manifest);
      fclose($file);

      return $this->manifestPath;

   }

   /**
   * Move the ipa file to destination.
   *
   * @return string new IPA path.
   * @access protected
   */
   protected function move(){

      /**
      * Check if destination exist, if not create it
      */
      if (!is_dir($this->destination))
         mkdir($this->destination) or die("unable to create a folder.");

      /**
      * Move it
      */
      rename($this->ipa, $this->destination.'/'.$this->ipa);

      return $this->destination.'/'.$this->ipa;

   }

   /**
   * Create a download link for ios devices.
   *
   * @return string download link.
   * @access public
   */
   public function link($manifest = NULL){

      empty($manifest)? $link = "itms-services://?action=download-manifest&url=".$this->url."/".$this->destination.'/'.$this->appName.".plist" : $link = "itms-services://?action=download-manifest&url=".$this->url."/".$manifest;

      return $link;
   }

   /**
   * Clean the temp folder (extraxted ipa).
   *
   * @access public
   */
   public function clean(){

      $list = glob($this->temp.'/*'); // get all files (notice hidden files are not included here!)
      foreach ($list as $name) {
         if (is_dir($name)){
            if($name!=$this->temp."." or $name!=$this->temp."..")
               $this->rm($name);
         } else {
            unlink($name);
         }
      }
   }

   /**
   * Remove a folder.
   *
   * @access protected
   */
   protected function rm($dir){

      // Stackoverflow
      $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
      $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
      foreach($files as $file) {
         if ($file->isDir()){
            rmdir($file->getRealPath());
         } else {
            unlink($file->getRealPath());
         }
      }
      rmdir($dir);

   }

}

?>
