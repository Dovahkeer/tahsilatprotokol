<?php

namespace App\Services;

use App\Models\Hacizci;
use App\Models\Muvekkil;
use App\Models\Portfoy;
use App\Support\NameNormalizer;
use Illuminate\Support\Collection;

class LookupService
{
    public function listMuvekkiller(): Collection
    {
        return Muvekkil::query()
            ->where('aktif', true)
            ->orderBy('ad')
            ->get()
            ->map(fn (Muvekkil $muvekkil) => [
                'id' => (string) $muvekkil->id,
                'ad' => $muvekkil->ad,
                'kod' => $muvekkil->kod,
            ]);
    }

    public function listPortfoyler(null|int|string $muvekkilId = null): Collection
    {
        return Portfoy::query()
            ->when($muvekkilId, fn ($query) => $query->where('muvekkil_id', $muvekkilId))
            ->where('aktif', true)
            ->orderBy('ad')
            ->get()
            ->map(fn (Portfoy $portfoy) => [
                'id' => (string) $portfoy->id,
                'muvekkil_id' => (string) $portfoy->muvekkil_id,
                'ad' => $portfoy->ad,
                'kod' => $portfoy->kod,
            ]);
    }

    public function listHacizciler(): Collection
    {
        return Hacizci::query()
            ->where('aktif', true)
            ->orderBy('ad_soyad')
            ->get()
            ->map(fn (Hacizci $hacizci) => [
                'id' => (string) $hacizci->id,
                'ad_soyad' => $hacizci->ad_soyad,
                'sicil_no' => $hacizci->sicil_no,
                'kademe' => $hacizci->kademe,
            ]);
    }

    public function findOrCreateMuvekkil(string $ad, ?string $kod = null): Muvekkil
    {
        $normalized = NameNormalizer::normalize($ad);

        return Muvekkil::query()->firstOrCreate(
            ['normalized_ad' => $normalized],
            [
                'ad' => trim($ad),
                'kod' => $kod,
                'aktif' => true,
            ],
        );
    }

    public function findOrCreatePortfoy(Muvekkil $muvekkil, string $ad, ?string $kod = null): Portfoy
    {
        $normalized = NameNormalizer::normalize($ad);

        return Portfoy::query()->firstOrCreate(
            [
                'muvekkil_id' => $muvekkil->id,
                'normalized_ad' => $normalized,
            ],
            [
                'ad' => trim($ad),
                'kod' => $kod,
                'aktif' => true,
            ],
        );
    }
}
