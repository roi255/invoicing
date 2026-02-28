<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ViewEmailLog extends ViewRecord
{
    protected static string $resource = EmailLogResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Html::make(fn ($record) => view('filament.email-logs.view', compact('record'))->render()),
        ]);
    }
}
