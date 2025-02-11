<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ForwardResource\Pages;
use App\Filament\Resources\ForwardResource\RelationManagers;
use App\Models\Forward;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ForwardResource extends Resource
{
    protected static ?string $model = Forward::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageForwards::route('/'),
        ];
    }
}
