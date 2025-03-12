<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ForwardResource\Pages;
use App\Filament\Resources\ForwardResource\RelationManagers;
use App\Models\Document;
use App\Models\Forward;
use App\Models\User;
use Filament\Forms;
use App\Models\Log;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;



class ForwardResource extends Resource
{
    protected static ?string $model = Forward::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $modelLabel = 'Incoming Documents';

    protected static ?string $navigationLabel = 'Incoming Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sender')
                    ->maxLength(255),
                Forms\Components\TextInput::make('documentId')
                    ->maxLength(255),
                Forms\Components\TextInput::make('receiver')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function(){
                if(Auth::user()->hasRole('records')){
                    return Forward::query()->where('receiver_division',  Auth::user()->division)->where('status', 'Forwarded');

                }elseif(Auth::user()->hasRole('division_record')){
                    return Forward::query()->where('receiver_division', Auth::user()->division)->where('status', 'Forwarded')->orWhere('status', 'Routed');

                }elseif(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief')){
                    return Forward::query()->where('receiver', Auth::user()->id)->where('status', 'Released');

                }else{
                    return Forward::query()->where('created_at', null);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sender')
                    ->label('From')
                    ->formatStateUsing(
                        function(?string $state, $record){
                            switch($record->sender_division){
                                case 'OD':
                                    $div = 'Office of the Director';
                                    break;
                                case 'PM':
                                    $div = 'PMTSSD';
                                    break;
                                case 'PP':
                                    $div = 'PPDD';
                                    break;
                                case 'LS':
                                    $div = 'LSRAD';
                                    break;
                                default:
                                    $div = 'N/A';
                                    break;
                            }
                            return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname'). ' - '. $div;
                        }
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_name')
                    ->label('Document Name')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('document_description')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('receive_div')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->label('Receive Document')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Receipt')
                    ->modalDescription('Are you sure you want to receive this document?')
                    ->modalSubmitActionLabel('Yes, Receive') // Customize submit button label
                    ->hidden(function(){
                        if(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief')){
                            return true;
                        }else{
                            return false;
                        }
                    })
                    ->action(function (Forward $record): void {
                        $document = Document::query()->where('rms_id', $record->documentId)->first();
                        $document->status = 'Received - Division';
                        $document->holder_division = Auth::user()->division;
                        $document->save();

                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                        $log = Log::create([
                            'id' => $uuid,
                            'docId' => $document->rms_id,
                            'doc_name' => $document->subject,
                            'doc_description' => $document->description,
                            'doc_type' => $document->docType,
                            'user' => Auth::user()->id,
                            'user_division' => Auth::user()->division,
                            'transaction' => 'Received - Division',
                            'recipient' => 'N/A',
                            'recipient_division' => 'N/A',
                        ]);
                        if($record->receiver_divisionTemp){
                            $record->status = 'Received - Internal';
                            $temp = $record->receiver_division;
                            $record->receiver_division = $record->receiver_divisionTemp;
                            $record->receiver_divisionTemp = $temp;

                            $document->status = 'Received - Internal';
                            $document->save();

                            $log->transaction = 'Received - Internal';
                            $log->save();
                        }else{
                            $record->status = 'Received - Internal';
                        }
                        
                        $record->save();
                        // $record->delete();
                    }),
                Action::make('receive_emp')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->label('Receive Document.')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Receipt')
                    ->modalDescription('Are you sure you want to receive this document?')
                    ->modalSubmitActionLabel('Yes, Receive') // Customize submit button label
                    ->hidden(function(){
                        if(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief')){
                            return false;
                        }else{
                            return true;
                        }
                    })
                    ->action(function(Forward $record){
                        $document = Document::query()->where('rms_id', $record->documentId)->first();
                        $document->status = 'Received';
                        $document->holder_user = Auth::user()->id;
                        $document->forward_id = NULL;
                        $document->save();

                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                        $log = Log::create([
                            'id' => $uuid,
                            'docId' => $document->rms_id,
                            'doc_name' => $document->subject,
                            'doc_description' => $document->description,
                            'doc_type' => $document->docType,
                            'user' => Auth::user()->id,
                            'user_division' => Auth::user()->division,
                            'transaction' => 'Received',
                            'recipient' => 'N/A',
                            'recipient_division' => 'N/A',
                        ]);

                        $record->delete();
                    })
            ])
            ->emptyStateHeading('You Have No Incoming Documents')
            ->emptyStateDescription('Waiting for Documents directed to you. Chill for a while')
            ->emptyStateIcon('heroicon-o-bookmark')
            // ->emptyStateActions([
            //     Action::make('run')
            //         ->label('DD')
            //         ->action(function(){
            //             dd(Auth::user()->id);
            //             return;
            //         })
            //         ->icon('heroicon-m-plus')
            //         ->button(),
            // ])
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageForwards::route('/'),
        ];
    }
}
