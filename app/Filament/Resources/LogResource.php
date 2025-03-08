<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogResource\Pages;
use App\Filament\Resources\LogResource\RelationManagers;
use App\Models\Log;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $modelLabel = 'Document Logs';

    protected static ?string $navigationLabel = 'Document Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('docId')
                    ->maxLength(255),
                Forms\Components\TextInput::make('transaction')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sender')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dateTime')
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

                Tables\Columns\TextColumn::make('docId')
                    ->searchable()
                    ->label('Id'),
                Tables\Columns\TextColumn::make('transaction')
                    ->searchable()
                    ->label('Transaction Type'),
                Tables\Columns\TextColumn::make('sender')
                    ->searchable()
                    ->label('Sent by')
                    ->formatStateUsing(
                        function(?string $state){
                            return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname');
                        }
                    ),
                Tables\Columns\TextColumn::make('receiver')
                    ->searchable()
                    ->formatStateUsing(
                        function(?string $state){
                            return User::where('id', $state)->value('firstname') . ' ' . User::where('id', $state)->value('lastname');
                        }
                    )
                    ->label('Received by'),
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
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageLogs::route('/'),
        ];
    }
}
