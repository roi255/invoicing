<?php

namespace App\Filament\Pages;

use App\Enums\PaymentMethod;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{

    protected static ?string $navigationLabel = 'Settings';
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.manage-settings';

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return Heroicon::Cog6Tooth;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Company Information
            'company_name'           => Setting::get('company_name', config('app.name')),
            'company_email'          => Setting::get('company_email', config('mail.from.address')),
            'company_phone'          => Setting::get('company_phone'),
            'company_address'        => Setting::get('company_address'),
            // Invoice Defaults
            'default_tax_rate'       => Setting::get('default_tax_rate', '0'),
            'default_payment_terms'  => Setting::get('default_payment_terms', '30'),
            'invoice_prefix'         => Setting::get('invoice_prefix', 'INV'),
            // Payment Information
            'default_payment_method' => Setting::get('default_payment_method', 'bank_transfer'),
            'bank_name'              => Setting::get('bank_name'),
            'bank_account_name'      => Setting::get('bank_account_name'),
            'bank_account_number'    => Setting::get('bank_account_number'),
            'bank_routing_number'    => Setting::get('bank_routing_number'),
            'bank_iban'              => Setting::get('bank_iban'),
            'bank_swift'             => Setting::get('bank_swift'),
            'payment_link'           => Setting::get('payment_link'),
            // Invoice Appearance
            'default_notes'          => Setting::get('default_notes'),
            'invoice_terms'          => Setting::get('invoice_terms'),
            // Currency & Locale
            'currency_code'          => Setting::get('currency_code', 'usd'),
            'currency_symbol'        => Setting::get('currency_symbol', '$'),
            // Reminder Schedule
            'reminder_interval_days' => Setting::get('reminder_interval_days', '7'),
            'reminder_max_count'     => Setting::get('reminder_max_count', '3'),
            // Email Customization
            'email_greeting'         => Setting::get('email_greeting', 'Dear'),
            'email_signature'        => Setting::get('email_signature', 'Thank you for your business.'),
            'email_reply_to'         => Setting::get('email_reply_to'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Information')
                    ->description('These details appear on invoices and outgoing emails.')
                    ->columns(2)
                    ->components([
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('company_email')
                            ->label('Company Email')
                            ->email()
                            ->required()
                            ->maxLength(150),

                        TextInput::make('company_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(30),

                        Textarea::make('company_address')
                            ->label('Company Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Invoice Defaults')
                    ->description('Default values pre-filled on new invoices.')
                    ->columns(3)
                    ->components([
                        TextInput::make('invoice_prefix')
                            ->label('Invoice Prefix')
                            ->required()
                            ->maxLength(10)
                            ->helperText('e.g. INV, ROI, ACME'),

                        TextInput::make('default_tax_rate')
                            ->label('Default Tax Rate')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),

                        TextInput::make('default_payment_terms')
                            ->label('Payment Terms')
                            ->numeric()
                            ->step(1)
                            ->suffix('days')
                            ->minValue(0)
                            ->helperText('Due date = invoice date + this many days'),
                    ]),

                Section::make('Payment Information')
                    ->description('Bank details shown on invoices and emails.')
                    ->columns(2)
                    ->components([
                        Select::make('default_payment_method')
                            ->label('Default Payment Method')
                            ->options(PaymentMethod::class),

                        TextInput::make('payment_link')
                            ->label('Payment Link')
                            ->url()
                            ->maxLength(255)
                            ->helperText('e.g. https://paypal.me/yourname'),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(100),

                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(100),

                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(50),

                        TextInput::make('bank_routing_number')
                            ->label('Routing / Sort Code')
                            ->maxLength(50),

                        TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->maxLength(50),

                        TextInput::make('bank_swift')
                            ->label('SWIFT / BIC')
                            ->maxLength(20),
                    ]),

                Section::make('Invoice Appearance')
                    ->description('Default text pre-filled on new invoices and shown on PDFs.')
                    ->components([
                        Textarea::make('default_notes')
                            ->label('Default Notes')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Textarea::make('invoice_terms')
                            ->label('Terms & Conditions')
                            ->rows(4)
                            ->maxLength(5000)
                            ->helperText('Shown at the bottom of the PDF.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Currency & Locale')
                    ->description('Currency used across all money fields.')
                    ->columns(2)
                    ->components([
                        TextInput::make('currency_code')
                            ->label('Currency Code')
                            ->maxLength(3)
                            ->required()
                            ->helperText('ISO 4217 code, e.g. usd, eur, kes'),

                        TextInput::make('currency_symbol')
                            ->label('Currency Symbol')
                            ->maxLength(5)
                            ->required()
                            ->helperText('e.g. $, €, KSh'),
                    ]),

                Section::make('Reminder Schedule')
                    ->description('Controls automatic payment reminder frequency.')
                    ->columns(2)
                    ->components([
                        TextInput::make('reminder_interval_days')
                            ->label('Reminder Interval')
                            ->numeric()
                            ->step(1)
                            ->minValue(1)
                            ->suffix('days'),

                        TextInput::make('reminder_max_count')
                            ->label('Max Reminders per Invoice')
                            ->numeric()
                            ->step(1)
                            ->minValue(1),
                    ]),

                Section::make('Email Customization')
                    ->description('Personalise outgoing invoice and reminder emails.')
                    ->columns(2)
                    ->components([
                        TextInput::make('email_greeting')
                            ->label('Greeting')
                            ->maxLength(50)
                            ->helperText('e.g. Dear, Hi, Hello'),

                        TextInput::make('email_reply_to')
                            ->label('Reply-To Address')
                            ->email()
                            ->maxLength(150)
                            ->helperText('Defaults to company email if blank.'),

                        Textarea::make('email_signature')
                            ->label('Email Signature')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon(Heroicon::Check)
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::setMany($data);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
