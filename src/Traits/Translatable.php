<?php

namespace Said\Translatable\Traits;

use Illuminate\Database\Eloquent\Model;
use Said\Translatable\Models\Translation;

/**
 * Description of Translatable
 * 
 * @author Said
 */
trait Translatable
{
    private static $trans;
    protected $defaultLocale;
    protected $selectedLocale;

    protected static function boot() {
        parent::boot();
        static::deleting(function($model) {
            $model->translations()->delete();
        });
    }
    /*
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     * */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
    /**
     * @return string
     */
    protected function locale() : string
    {
        return $this->defaultLocale=(!$this->defaultLocale) ? app()->getLocale() : $this->defaultLocale;
    }

    /**
     * @param mixed $with_fall_back=false
     * 
     * @return void
     */
    public function init($with_fall_back=false) : void
    {
        if ($this->selectedLocale != $this->defaultLocale) {
            self::$trans = [];
        }
        if (!isset(self::$trans[$this->id])) {
            $this->selectedLocale = $this->locale();
            $fallBackLocale = config('translatable.fallback_locale');
            if(!$with_fall_back){
                self::$trans[$this->id] = $this->getTranslationCollection($this->selectedLocale);
            }else{
                self::$trans[$this->id] = (count($this->getTranslationCollection($this->selectedLocale))>0) ? $this->getTranslationCollection($this->selectedLocale) : $this->getTranslationCollection($fallBackLocale);
            }
        }
    }

    /**
     * @param mixed $column
     * @param null $default
     * @param bool $fallBack
     * 
     */
    public function translate($column, $default = null, $fallBack = false)

    {

        $this->init($fallBack);
        $default=($default) ? $default : @$this->attributes[$column];
        return self::$trans[$this->id][$column] ?? $default;
    }
    /**
     * @param mixed $locale
     * 
     */
    public function in($locale)
    {
        $this->defaultLocale=$locale;
        return $this;
    }
    /**
     * @param mixed $locale
     * 
     */
    public function getTranslationCollection($locale){
        return $this->translations->where('locale', $locale)->pluck('content', 'column');
    }
    public function getColumnTranslation($locale,$column){
        return $this->translations->where('locale', $locale)->where('column',$column)->first();
    }

    public function isTranslatable($column)

    {

        if (in_array('*', $this->translatable_columns)) return true;
        return in_array($column, $this->translatable_columns);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableAttributes()
        );

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Next we will handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes,
            $mutatedAttributes
        );

        // Here we will grab all of the appended, calculated attributes to this model
        // as these attributes are not really in the attributes array, but are run
        // when we need to array or JSON the model for convenience to the coder.
        foreach ($this->getArrayableAppends() as $key) {
            $attributes[$key] = $this->mutateAttributeForArray($key, null);
        }

        $attributes = $this->addTranslateAttributesToArray($attributes);

        return $attributes;
    }
    public function getAttribute($key)
    {
        if ($this->isTranslatable($key)) {
            return $this->translate($key,null,true);
        }

        return parent::getAttribute($key);
    }

    /**
     * @param mixed $attributes
     * @return array
     */
    function addTranslateAttributesToArray($attributes)
    {
        if (!in_array('*', $this->translatable_columns)) {
            foreach ($this->translatable_columns as $column) {
                if (isset($attributes[$column])) $attributes[$column] = $this->translate($column, null,true);
            }
        } else {
            foreach ($attributes as $column => $value) {
                $attributes[$column] = $this->translate($column, null,true);
            }
        }
        return $attributes;
    }

    /**
     * Transform a raw model value using mutators, casts, etc.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function transformModelValue($key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        if ($this->isTranslatable($key)) {
            return $this->translate($key, $value,true);
        }
        if (
            $value !== null
            && \in_array($key, $this->getDates(), false)
        ) {
            return $this->asDateTime($value);
        }

        return $value;
    }
    /**
     * @param mixed $key
     * 
     */
    public function __get($key)
    {
        if (isset($this->translatable_columns) && in_array($key, $this->translatable_columns)) {
            return $this->translate($key,null,true);
        } else {
            return parent::__get($key);
        }
    }

    public function __set($key, $value)
    {
        if (isset($this->translatable_columns) && in_array($key, $this->translatable_columns)) {
            $this->translate($key, $value);
        } else {
            parent::__set($key, $value,true);
        }
    }
    /**
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getTranslationOrNew($locale,$column)
    {
        if (($translation = $this->getColumnTranslation($locale,$column)) === null) {
            $translation = $this->getNewTranslation($locale,$column);
        }
        return $translation;
    }
    /**
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getNewTranslation($locale,$column)
    {
        $translation = new Translation();
        $translation->setAttribute('locale', $locale);
        $translation->setAttribute('column', $column);
        $translation->setAttribute('translatable_type', get_class($this));
        $this->translations->add($translation);
        return $translation;
    }
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function isKeyALocale($key)
    {
        $locales = config('translatable.locales',[]);

        return in_array($key, $locales);
    }
    /**
     * @param $key
     *
     * @return array
     */
    private function getAttributeAndLocale($key)
    {
        if (str_contains($key, ':')) {
            return explode(':', $key);
        }

        return [$key, app()->getLocale()];
    }
    /**
     * @return bool
     */
    protected function saveTranslations()
    {
        $saved = true;
        foreach ($this->translations as $translation) {
            if ($saved && $this->isTranslationDirty($translation)) {
                $translation->setAttribute('translatable_id', $this->getKey());
                $saved = $translation->save();
            }
        }

        return $saved;
    }
    /**
     * @param \Illuminate\Database\Eloquent\Model $translation
     *
     * @return bool
     */
    protected function isTranslationDirty(Model $translation)
    {
        $dirtyAttributes = $translation->getDirty();
        unset($dirtyAttributes['locale']);

        return count($dirtyAttributes) > 0;
    }

    public function createOrUpdateTranslation(array $attributes){
        info(json_encode($attributes));
        foreach ($attributes as $key => $values) {
            info($key);
            info('values');
            info(json_encode($values));
            if ($this->isKeyALocale($key)) {
                foreach($values as $column=>$value){
                    if(empty($value)) continue;
                    $this->getTranslationOrNew($key,$column)->fill(['content' => $value]);
                }
            }
        }
        $this->saveTranslations();
    }
    public function scopeWhereTranslation($query,$value,$column=null,$locale=null){
        $query->whereHas('translations',function($q) use($column,$value,$locale){
            $q->when(!empty($column),function($q) use($column){ $q->where('column',$column);});
            $q->when(!empty($value),function($q) use($value){ $q->where('content','LIKE',"%{$value}%");});
            $q->when(!empty($locale),function($q) use($locale){ $q->where('locale',$locale);});
        });
    }
    public function scopeHasTranslations($query,$column=null,$locale=null){
        $query->whereHas('translations',function($q) use($column,$locale){
            $q->when(!empty($column),function($q) use($column){ $q->where('column',$column);});
            $locales=($locale) ? [$locale] : config('translatable.locales');
            $q->whereIn('locale',$locales);
        });
    }
    /**
     * @param array $attributes
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $values) {
            if ($this->isKeyALocale($key)) {
                foreach($values as $column=>$value){
                    if(empty($value)) continue;
                    $this->getTranslationOrNew($key,$column)->fill(['content' => $value]);
                }
                unset($attributes[$key]);
            }
        }
        unset($attributes[$this->getKeyName()]);
        return parent::fill($attributes);
    }
    /**
     * Deletes all translations for this model.
     *
     * @param string|array|null $locales The locales to be deleted (array or single string)
     *                                   (e.g., ["en", "de"] would remove these translations).
     */
    public function deleteTranslations($locales = null)
    {
        if ($locales === null) {
            $this->translations()->delete();
        } else {
            $locales = (array) $locales;
            $this->translations()->whereIn('locale', $locales)->delete();
        }
        // we need to manually "reload" the collection built from the relationship
        // otherwise $this->translations()->get() would NOT be the same as $this->translations
        $this->load('translations');
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if ($this->exists) {
            if (count($this->getDirty()) > 0) {
                // If $this->exists and dirty, parent::save() has to return true. If not,
                // an error has occurred. Therefore we shouldn't save the translations.
                if (parent::save($options)) {
                    return $this->saveTranslations();
                }

                return false;
            } else {
                // If $this->exists and not dirty, parent::save() skips saving and returns
                // false. So we have to save the translations
                if ($saved = $this->saveTranslations()) {
                    $this->fireModelEvent('saved', false);
                    $this->fireModelEvent('updated', false);
                }

                return $saved;
            }
        } elseif (parent::save($options)) {
            // We save the translations only if the instance is saved in the database.
            return $this->saveTranslations();
        }

        return false;
    }
}
