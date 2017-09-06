![www.Ali.ninja](https://raw.githubusercontent.com/x0ninja/IPA-Distributor/master/ipa.png "www.ali.ninja")

This class, will read the necessary information to create manifest which gives you the ability to distribute your IPA files on the fly!
This class is using: **[CFPropertyList](https://github.com/rodneyrehm/CFPropertyList)**
___
Usage
=====
- Load IPA and Do everthing:
```$ipa = new aAlharthi\Ninja\IPA('file.ipa', true); // Auto=true```
	- Load IPA only:
	```$ipa = new aAlharthi\Ninja\IPA('file.ipa');```

- Extract IPA and read necessary info only:
```$ipa->eIPA(false, false); // (MOVE?, ICON?)```
	- Move the file to
	```$destination``` after: ```$ipa->eIPA(true, false);```
	- Copy the app icon as well:
	```$ipa->eIPA(false, true);```
	
- Generate a manifest: ```$ipa->generate();```

- Print a manifest: ```echo $ipa->manifest;```
	- Manifest path: ```echo $ipa->manifestPath;```

- Read a manifest and get necessary info:
```$ipa->read('manifest.plist');``` returns:
```
Array{ 'appIPA' => 'The app ipa file path', 'appName' => 'The app name', 'appVersion' => 'The app version', 'appBundleID' => 'The app bundle identifier' }
```

- Get full download link (IPA):
```echo $ipa->link();```
	- From a manifest:
	```echo $ipa->link('manifest.plist');``` returns:
```
itms-services://?action=download-manifest&url=https://www.example.com/ $destination /manifest.plist
```

- Clean temp folder: ```$ipa->clean();```

- Print the app name: ```echo $ipa->appName;``` or ```$results = $ipa->read(); echo $results['appName'];```

- Print the app version: ```echo $ipa->appVersion;``` or ```$results = $ipa->read(); echo $results['appVersion'];```

- Print the app bundle identifier: ```echo $ipa->appBundleID;``` or ```$results = $ipa->read(); echo $results['appBundleID'];```

- Print the ipa URL: ```echo $ipa->appIPA;``` or ```$results = $ipa->read(); echo $results['appIPA'];```

___
Examples:
=====
> **To run it automatically for every .ipa file in the current dir:**
```
<?php
require_once("ipa.php");
foreach(glob('*.ipa') as $file){
	$ipa = new aAlharthi\Ninja\IPA($file, true); // Auto=true
}
?>
```
Everything should be in the ```$destination``` folder (icons, IPAs, and manifests').
___
> **To distribute the apps:**
```
<?php
require_once("ipa.php");
foreach (glob('*.plist') as $manifest){
	$ipa =  new aAlharthi\Ninja\IPA(); // Do not load any IPA file, already done in this example
	$app = $ipa->read($manifest);
	echo '<a href="'.$ipa->link().'">'.$ipa->appName.'</a><br>'; // Create a download link (IOS devices)
}
?>
```
___
> **Accessing the app info:**
```
<?php
require_once("ipa.php");
$ipa = new aAlharthi\Ninja\IPA('file.ipa');
$ipa->eIPA(false, false); // do not move the ipa and do not copy the icon 
$app = $ipa->read();
echo $ipa->appName; // Returns the app name (Direct)
echo $app['appName'];  // Returns the app name (Returned array from read())
?>
```
___

Bugs
=====
- E-mail: **```root@ali.ninja```**
