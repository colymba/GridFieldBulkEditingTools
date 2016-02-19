<?php
/**
 * Phing build task used to generate SilverStripe translation files
 * from Transifex data. This tasks assumes that:
 * - Javascript translations are from the Transifex resource called 'js'
 * - YML translations are from the Transifex resource called 'yml'
 * - Transifex AUTH credentials to be saved in $txAuthFile with content {"username": "user", "password": "pwd"}.
 *
 * This is inspired by SilverStripe build tools. Thanks
 *
 * @see https://github.com/silverstripe/silverstripe-buildtools/blob/master/src/GenerateJavascriptI18nTask.php
 */
// Ignore this file if phing is not installed
if (!class_exists('Task')) {
    return;
}

include_once 'phing/Task.php';

class BuildTransifexTranslations extends Task
{
    private $txapi = 'https://www.transifex.com/api/2';
    private $txproject = '';
    private $txAuthFile = 'transifexAuth.json';
    private $txAuth = null;

    private $root = '';
    private $jsDir = '/lang/js';
    private $ymlDir = '/lang';

    public function settxapi($txapi)
    {
        $this->txapi = $txapi;
    }

    public function settxproject($txproject)
    {
        $this->txproject = $txproject;
    }

  /**
   * Task init.
   */
  public function init()
  {
      $root = realpath(__DIR__.DIRECTORY_SEPARATOR.'..');
      $authFile = $root.DIRECTORY_SEPARATOR.$this->txAuthFile;

      if (file_exists($authFile)) {
          $txAuthData = file_get_contents($authFile);
          $txAuthData = json_decode($txAuthData);
          if ($txAuthData->username && $txAuthData->password) {
              $this->txAuth = $txAuthData;
          } else {
              throw new BuildException("Transifex credentials malformat. Check your $authFile for 'username' and 'password' keys.");
          }
      } else {
          throw new BuildException("Transifex credentials not found. $authFile missing.");
      }

      $this->root = $root;
      $this->jsDir = $root.$this->jsDir;
      $this->ymlDir = $root.$this->ymlDir;
  }

  /**
   * Let's get to buisness...
   */
  public function main()
  {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $this->txAuth->username.':'.$this->txAuth->password);

    // get resources
    $url = $this->txapi.'/project/'.$this->txproject.'/resources/';
      curl_setopt($ch, CURLOPT_URL, $url);
      $resources = curl_exec($ch);

      if (!$resources) {
          throw new BuildException('Cannot fetch resources');
      } else {
          $resources = json_decode($resources);
      }

    // get langs
    $url = $this->txapi.'/project/'.$this->txproject.'/languages/';
      curl_setopt($ch, CURLOPT_URL, $url);
      $languages = curl_exec($ch);

      if (!$languages) {
          throw new BuildException('Cannot fetch languages');
      } else {
          $languages = json_decode($languages);
      }

    // clear existing translation files and/or setup folders
    $this->resetTranslations();

    // add source_language_code to languages list
    $sourceLangs = array();
      foreach ($resources as $resource) {
          $lang = new StdClass();
          $locale = $resource->source_language_code;
          $lang->language_code = $locale;
          if (!array_key_exists($locale, $sourceLangs)) {
              $sourceLangs[$locale] = $lang;
          }
      }
      $sourceLangs = array_values($sourceLangs);
      $languages = array_merge($languages, $sourceLangs);

    // get each resource translations
    foreach ($resources as $resource) {
        foreach ($languages as $language) {
            $url = $this->txapi.'/project/'.$this->txproject.'/resource/'.$resource->slug.'/translation/'.$language->language_code;
            curl_setopt($ch, CURLOPT_URL, $url);
            $data = curl_exec($ch);
            if ($data) {
                $this->saveTranslation($resource->slug, $language->language_code, $data);
            }
        }
    }

      curl_close($ch);
  }

  /**
   * Clear any existing translation files
   * and create directory structure if needed.
   */
  private function resetTranslations()
  {
      if (file_exists($this->jsDir)) {
          echo "Clearing js translations...\n";
          $iterator = new GlobIterator($this->jsDir.DIRECTORY_SEPARATOR.'*.js');
          foreach ($iterator as $fileInfo) {
              if ($fileInfo->isFile()) {
                  $del = unlink($fileInfo->getRealPath());
              }
          }
      }

      if (file_exists($this->ymlDir)) {
          echo "Clearing yml translations...\n";
          $iterator = new GlobIterator($this->ymlDir.DIRECTORY_SEPARATOR.'*.yml');
          foreach ($iterator as $fileInfo) {
              if ($fileInfo->isFile()) {
                  $del = unlink($fileInfo->getRealPath());
              }
          }
      }

      if (!file_exists($this->jsDir)) {
          echo "Creating js folders...\n";
          mkdir($this->jsDir);
      }

      if (!file_exists($this->ymlDir)) {
          echo "Creating yml folders...\n";
          mkdir($this->ymlDir);
      }
  }

  /**
   * Hook that detect the translation type via resource slug
   * and call corect saving function with data.
   *
   * @param  string $resource Transifex resrouce slug
   * @param  string $locale   Transifex locale
   * @param  string $data     Raw Transifex translation data
   */
  private function saveTranslation($resource, $locale, $data)
  {
      if (!$resource || !$locale || !$data) {
          return;
      }

      $data = json_decode($data);
      $translation = rtrim($data->content);

      switch ($resource) {
      case 'js':
        $this->saveJSTranslation($locale, $translation);
        break;

      case 'yml':
        $this->saveYMLTranslation($locale, $translation);
        break;
    }
  }

  /**
   * Save a JS translation file
   * Uses JSTemplate to fit with SilverStripe requirements.
   *
   * @param  string $locale Locale code
   * @param  string $json   JSON translation key:value
   */
  private function saveJSTranslation($locale, $json)
  {
      echo "Saving $locale.js\n";
      file_put_contents(
      $this->jsDir.DIRECTORY_SEPARATOR.$locale.'.js',
      $this->getBanner('js').
      str_replace(
        array(
          '%TRANSLATIONS%',
          '%LOCALE%',
        ),
        array(
          $json,
          $locale,
        ),
        $this->getJSTemplate()
      )
    );
  }

  /**
   * Save a YML translation file.
   *
   * @param  string $locale Locale code
   * @param  string $yml    YML translation
   */
  public function saveYMLTranslation($locale, $yml)
  {
      echo "Saving $locale.yml\n";

      if ($locale !== 'en') {
          $content = $this->getBanner('yml').$yml;
      } else {
          $content = $yml;
      }

      file_put_contents(
      $this->ymlDir.DIRECTORY_SEPARATOR.$locale.'.yml',
      $content
    );
  }

  /**
   * Return the commented file banner.
   *
   * @param  string $type File type e.g js
   *
   * @return string       The commented file banner
   */
  private function getBanner($type)
  {
      switch (strtolower($type)) {
      case 'yml':
        $comment = '#';
        break;

      default:
        $comment = '//';
        break;
    }

      $banner = <<<TMPL
$comment DO NOT MODIFY. Generated by build task.
$comment Contribute here: https://www.transifex.com/projects/p/gridfieldbulkeditingtools/

TMPL;

      return $banner;
  }

  /**
   * Return the SilverStripe JS lang file template.
   *
   * @return string The JS file template
   */
  private function getJSTemplate()
  {
      $tmpl = <<<TMPL
if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
  if(typeof(console) != 'undefined') console.error('Class ss.i18n not defined');
} else {
  ss.i18n.addDictionary('%LOCALE%', %TRANSLATIONS%);
}
TMPL;

      return $tmpl;
  }
}
