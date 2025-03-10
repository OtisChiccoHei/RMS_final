<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        session()->put('doc_view', 'all');
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('mine')
                ->label('View My Documents')
                ->hidden(function(){
                    if(session()->get('doc_view') == 'mine' && (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('records'))){
                        return true;
                    }else{
                        if(!(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('records'))){
                            return true;
                        }
                        return false;
                    }
                })
                ->color('success')
                ->action(function(){
                    session()->put('doc_view', 'mine');
                    $this->resetTable();
                    return;
                }),
            
            Actions\Action::make('all')
                ->label('View All Documents')
                ->hidden(function(){
                    if(session()->get('doc_view') == 'all' && (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('records'))){
                        return true;
                    }else{
                        if(!(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('records'))){
                            return true;
                        }
                        return false;
                    }
                })
                ->color('info')
                ->action(function(){
                    session()->put('doc_view', 'all');
                    $this->resetTable();
                    return;
                })
        ];
    }
}
