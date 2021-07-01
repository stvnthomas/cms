<?php

namespace Statamic\Structures;

use Statamic\Entries\AugmentedEntry;
use Statamic\Statamic;

class AugmentedPage extends AugmentedEntry
{
    protected $page;
    protected $hasEntry = false;

    public function __construct($page)
    {
        $this->page = $page;

        if ($page->reference() && $page->referenceExists()) {
            $this->hasEntry = true;
            parent::__construct($page->entry());
        } else {
            parent::__construct($page);
        }
    }

    public function keys()
    {
        $keys = collect($this->hasEntry
            ? parent::keys()
            : ['title', 'url', 'uri', 'permalink']);

        $keys = $keys->merge($this->page->data()->keys());

        $keys = Statamic::isApiRoute() ? $this->apiKeys($keys) : $keys;

        return $keys->unique()->sort()->values()->all();
    }

    private function apiKeys($keys)
    {
        return collect($keys)
            ->reject(function ($key) {
                return in_array($key, ['parent']);
            });
    }

    protected function getFromData($key)
    {
        if ($key === 'title') {
            return $this->page->title();
        }

        return $this->page->value($key);
    }

    protected function blueprintFields()
    {
        $fields = $this->page->blueprint()->fields()->all();

        if ($this->page !== $this->data) {
            $entryFields = $this->data->blueprint()->fields()->all();
            $fields = $entryFields->merge($fields);
        }

        return $fields;
    }
}
