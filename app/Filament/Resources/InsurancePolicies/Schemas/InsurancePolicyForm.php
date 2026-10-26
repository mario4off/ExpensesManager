<?php

namespace App\Filament\Resources\InsurancePolicies\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Document;
use App\Models\InsurancePolicy;
use App\Services\DocumentStorage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class InsurancePolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('policy_number')
                    ->label('Número de póliza')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('holder_name')
                    ->label('Tomador')
                    ->required()
                    ->maxLength(255),
                TextInput::make('insurer')
                    ->label('Aseguradora')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('starts_on')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('ends_on')
                    ->label('Fecha de fin')
                    ->required()
                    ->afterOrEqual('starts_on'),
                MoneyInput::make('premium')
                    ->label('Prima')
                    ->required(),
                FileUpload::make('document_id')
                    ->label('Documento')
                    ->acceptedFileTypes(['application/pdf', 'image/png'])
                    ->maxSize(10240)
                    ->fetchFileInformation(false)
                    ->downloadable()
                    ->openable()
                    ->getUploadedFileUsing(function (string $file, ?InsurancePolicy $record): ?array {
                        // The field state comes from the client: only the record's own document is shown.
                        $document = (string) $record?->document_id === $file ? Document::find($file) : null;

                        if (! $document) {
                            return null;
                        }

                        return [
                            'name' => $document->original_name,
                            'size' => 0,
                            'type' => str_ends_with($document->path, '.pdf') ? 'application/pdf' : 'image/png',
                            'url' => Storage::disk('s3')->temporaryUrl($document->path, now()->addMinutes(30)),
                        ];
                    })
                    ->deleteUploadedFileUsing(function (string $file, ?InsurancePolicy $record): void {
                        // The field state comes from the client: only the record's own document can be deleted.
                        abort_unless((string) $record?->document_id === $file, 403);

                        $document = Document::findOrFail($file);

                        DB::transaction(function () use ($document) {
                            InsurancePolicy::where('document_id', $document->id)->update(['document_id' => null]);

                            app(DocumentStorage::class)->delete($document);
                        });
                    })
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        try {
                            return (string) app(DocumentStorage::class)->store($file, 'insurance_policies')->getKey();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('No se pudo subir el documento. No se ha guardado el registro; inténtalo de nuevo.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }
                    }),
            ]);
    }
}
