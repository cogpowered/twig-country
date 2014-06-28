<?php

namespace CogPowered\Twig\Extension\Country;

use Twig_Extension;
use Aura\Html\HelperLocatorFactory;
use Twig_SimpleFunction;

class Form extends Twig_Extension
{
    /**
     * @var \Aura\Html\HelperLocatorFactory
     */
    protected $html;

    /**
     * @var array country codes & names.
     */
    protected $countries;

    public function getHtml()
    {
        if (!empty($this->html)) {
            return $this->html;
        }

        $factory = new HelperLocatorFactory;
        return $factory->newInstance();
    }

    /**
     * Set the html form builder instance.
     *
     * @param \Aura\Html\HelperLocatorFactory $html
     *
     * @return void
     */
    public function setHtml(HelperLocatorFactory $html)
    {
        $this->html = $html;
    }

    /**
     * Retrieve country listing for that locale.
     *
     * @param string $locale
     *
     * @return array
     */
    public function getCountries($locale)
    {
        if (!empty($this->countries)) {
            return $this->countries;
        }

        $country_list_path = __DIR__.'/../../../umpirsky/country-list/country/icu/';
        $path              = $country_list_path.$locale.'/country.php';

        // Does that locale exist
        if (file_exists($path)) {
            $countries = include $path;
        } else {
            // Fallback to English
            $countries = include $country_list_path.'en/country.php';
        }

        return $countries;
    }

    /**
     * Set the countries.
     *
     * @param array $countries
     *
     * @return void
     */
    public function setCountries(array $countries)
    {
        $this->countries = $countries;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cog_twig_extension_country_form';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('form_country', [$this, 'formCountry'], ['is_safe' => ['html']]),
        );
    }

    /**
     * Output a country select input.
     *
     * @param string $name       Sets the name attribute on the select.
     * @param string $selected   ISO code of the selected country.
     * @param array  $attributes Sets attributes on the select. Can also set placeholder, for the first element.
     * @param string $locale     Locale of the countries. Falls back to `en`.
     *
     * @return string
     */
    public function formCountry($name, $selected = '', array $attributes = array(), $locale = 'en')
    {
        $html      = $this->getHtml();
        $countries = $this->getCountries($locale);

        // If nothing selected, make sure the placeholder is used
        if ($selected === null) {
            $selected = '';
        }

        // Build select input
        $select = $html->input(array(
            'type'    => 'select',
            'name'    => $name,      // aura.html -> this doesn't actually work
            'value'   => $selected,  // doesn't work either
            'options' => $countries,
        ));

        // Set attributes
        // Fixes a bug where the name & value are ignored
        // The need for this should disappear
        if (!isset($attributes['name'])) {
            $attributes['name'] = $name;
        }

        $select->attribs($attributes);
        $select->selected($selected);

        return (string) $select;
    }
}
