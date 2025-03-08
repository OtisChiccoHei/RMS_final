<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ForwardResource\Pages;
use App\Filament\Resources\ForwardResource\RelationManagers;
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
                ->formatStateUsing(
                    function(?string $state){
                        return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname');
                    }
                )
                    ->searchable(),
                Tables\Columns\TextColumn::make('documentId')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receiver')
                ->formatStateUsing(
                    function(?string $state){
                        return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname');
                    }
                )
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Receive Document')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Receipt')
                    ->modalDescription('Are you sure you want to receive this document?')
                    ->modalSubmitActionLabel('Yes, Receive') // Customize submit button label
                    ->action(function (Forward $record): void {
                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'sent-' . substr($uuid, 0, 12) . '-' . $microseconds;
            
                        $temp = Forward::create([
                            'id' => $uuid,
                            'sender' => $record->holder, // Use the current holder as the sender
                            'receiver' => Auth::user()->id, // Set the receiver to the current user
                            'documentId' => $record->rmsid,
                            'status' => 'Received',
                        ]);
            
                        $uuid = Uuid::uuid4()->toString();
                        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                        $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
            
                        $log = Log::create([
                            'id' => $uuid,
                            'docId' => $record->rmsid,
                            'transaction' => 'Received',
                            'sender' => $record->holder, // Use the current holder as the sender
                            'receiver' => Auth::user()->id, // Set the receiver to the current user
                        ]);
            
                        $record->holder = null;
                        $record->save();
                    })
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageForwards::route('/'),
        ];
    }
}
