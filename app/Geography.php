<?php

namespace App;

class Geography
{
    private array $data;

    public function __construct()
    {
        $this->data = json_decode(file_get_contents(resource_path('geography.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return array<string, string> */
    public function countries(): array
    {
        return $this->data['countries'];
    }

    /** @return array<string, string> */
    public function regions(?string $country): array
    {
        return $this->data['regions'][$country ?? ''] ?? [];
    }

    public function locationName(string $country, string $region = ''): string
    {
        $countryName = $this->countries()[$country];

        return $region === '' ? $countryName : $this->regions($country)[$region].', '.$countryName;
    }
}
