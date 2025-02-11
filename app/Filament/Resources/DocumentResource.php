<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\User;
use App\Models\Forward;
use App\Models\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;


class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('docType')
                            ->label('Document Type')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpan(2),
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('selectUpload')
                            ->label('Select File Type')
                            ->options([
                                'initial' => 'Initial Draft',
                                'final' => 'Final Draft',
                                'signed' => 'Signed Copy',
                            ])
                            ->live(),                    
                        Forms\Components\FileUpload::make('initialDraft')
                            ->hidden(function(Get $get){
                                if($get('selectUpload')=='initial'){
                                    return false;
                                }
                                else {
                                    return true;
                                }
                            })
                            ->label('Initial Draft'),
                        Forms\Components\FileUpload::make('finalDraft')
                            ->hidden(function(Get $get){
                                if($get('selectUpload')=='final'){
                                    return false;
                                }
                                else {
                                    return true;
                                }
                            })
                            ->label('Final Draft'),
                        Forms\Components\FileUpload::make('signedCopy')
                            ->hidden(function(Get $get){
                                if($get('selectUpload')=='signed'){
                                    return false;
                                }
                                else {
                                    return true;
                                }
                            })
                            ->label('Signed Copy'),                        
                    ]), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rmsid')
                    ->label('RMS ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description'),
                Tables\Columns\TextColumn::make('docType')
                    ->label('Document Type'),
                Tables\Columns\TextColumn::make('holder')
                    ->label('Current Holder')
                    ->formatStateUsing(
                        function(?string $state){
                            return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname');
                        }
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Action::make('Forward')
                    ->form([
                        Select::make('User')
                            ->label('Recipient')
                            ->options(User::query()->pluck('username', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data, Document $record): void {
                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'sent-' . substr($uuid, 0, 12) . '-' . $microseconds;
                        $temp= Forward::create([
                            'id'=> $uuid,
                            'sender'=> Auth::user()->id,
                            'receiver'=> $data['User'],
                            'documentId' => $record->rmsid,
                            'status'=> 'Pending',
                        ]);
                        
                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                        $log=Log::create([
                            'id' => $uuid,
                            'docId' => $record->rmsid,
                            'transaction' => 'Forwarded',
                            'sender' => Auth::user()->id,
                            'receiver' => $data['User'],
                        ]);
                        $record->holder=null;
                        $record->save();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDocuments::route('/'),
        ];
    }
}
