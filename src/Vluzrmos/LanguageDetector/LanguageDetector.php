<?php

namespace Vluzrmos\LanguageDetector;

use Illuminate\Http\Request;
use Vluzrmos\LanguageDetector\Contracts\LanguageDetector as DetectorContract;
use Symfony\Component\Translation\TranslatorInterface as Translator;

/**
 * Class LanguageDetector.
 */
class LanguageDetector implements DetectorContract
{
    /**
     * @var \Illuminate\Http\Request Illuminate (Laravel or Lumen) Request.
     */
    protected $request;
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface Illuminate Translator instance
     */
    protected $translator;
    /**
     * @var array Available Languages.
     */
    protected $availableLanguages;

    /**
     * Browser Language Detector.
     *
     * @param Request    $request            The request.
     * @param Translator $translator         Translator instance
     * @param array      $availableLanguages array of available languages.
     */
    public function __construct(Request $request, Translator $translator, array $availableLanguages)
    {
        $this->request = $request;
        $this->translator = $translator;
        $this->availableLanguages = $availableLanguages;
    }

    /**
     * Detect and apply the detected language.
     *
     * @param  bool $apply Default true, to apply the detected locale.
     *
     * @return string|null Returns the detected locale or null.
     */
    public function detect($apply = true)
    {
        $accept = $this->chooseBestLanguage();

        $language = $accept ? $this->getAliasedLocale($accept) : null;

        if ($apply && $language) {
            $this->setRealLocale($language);
        }

        return $language;
    }

    /**
     * Get accept languages.
     *
     * @return array
     */
    public function browserLanguages()
    {
        return $this->request->getLanguages();
    }

    /**
     * Get the languages for the application.
     *
     * @return array
     */
    public function appLanguages()
    {
        $languages = [];

        foreach ($this->availableLanguages as $key => $value) {
            $languages[] = $this->keyOrValue($key, $value);
        }

        return $languages;
    }

    /**
     * Get the $value if key is numeric or null, otherwise will return the key.
     *
     * @param string|int $key
     * @param mixed          $value
     *
     * @return mixed
     */
    public function keyOrValue($key, $value)
    {
        if (is_numeric($key) or empty($key)) {
            return $value;
        }

        return $key;
    }

    /**
     * Return the real locale based on available languages.
     *
     * @param string $locale
     * @return mixed
     */
    public function getAliasedLocale($locale)
    {
        return isset($this->availableLanguages[$locale]) ? $this->availableLanguages[$locale] : $locale;
    }

    /**
     * Set a Non-Aliased locale.
     *
     * @param string $locale
     * @return mixed
     */
    public function setRealLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * Get the best language between the browser and the application.
     *
     * @return array|null
     */
    public function chooseBestLanguage()
    {
        $accepted = array_intersect($this->browserLanguages(), $this->appLanguages());

        return $accepted ? array_shift($accepted) : null;
    }

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public function setLocale($locale)
    {
        $locale = $this->getAliasedLocale($locale);

        $this->setRealLocale($locale);

        return $locale;
    }
}
