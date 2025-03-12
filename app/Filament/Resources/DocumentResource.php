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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;


class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                            ->native(false)
                            ->options([
                                'initial' => 'Initial Draft',
                                'final' => 'Final Draft',
                                'signed' => 'Signed Copy',
                            ])
                            ->required()
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
                            ->required()
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
                            ->required()
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
                            ->required()
                            ->label('Signed Copy'),                        
                    ]), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(function(){
                if((Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('records')) && session()->get('doc_view') == 'all'){
                    return Document::query()
                                ->where('created_at', '!=', null);

                }elseif(Auth::user()->hasRole('super_admin') && session()->get('doc_view') == 'mine'){
                    return Document::query()
                                ->where('holder_user',  Auth::user()->id);

                }elseif(Auth::user()->hasRole('records') && session()->get('doc_view') == 'mine'){
                    return Document::query()
                                ->where('holder_division',  Auth::user()->division);

                }elseif(Auth::user()->hasRole('division_record')){
                    return Document::query()
                                ->where('holder_division', Auth::user()->division)
                                ->where(function ($query) {
                                    $query->where('status', 'Received - Division')
                                        ->orWhere('status', 'Received - Internal');
                                });

                }elseif(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief')){
                    return Document::query()
                                ->where('holder_user', Auth::user()->id);

                }else{
                    return Document::query()
                                ->where('created_at', null);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('rms_id')
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
                Tables\Columns\TextColumn::make('holder_user')
                    ->label('Current Holder')
                    ->formatStateUsing(
                        function(?string $state){
                            $user = User::where('id', $state)->first();
                            return $user->firstname .' '. $user->lastname;
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
                ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->label('View')
                        ->icon('heroicon-s-eye')
                        ->color('secondary')
                        // ->button()
                        ->disabledForm()
                        ->modalSubmitActionLabel('Done')
                        ->fillForm(
                            function (Document $record){
                                // dd($record->actionTaken);
                                return [
                                    'subject' => $record->subject ,
                                    'description' => $record->description ,
                                    'docType' => $record->docType ,
                                    'initialDraft' => $record->initialDraft ,
                                    'finalDraft' => $record->finalDraft ,
                                    'signedCopy' => $record->signedCopy ,
                                    'actionTaken' => $record->actionTaken,
                                ];
                            }
                        )
                        ->form([
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
                                    Forms\Components\FileUpload::make('initialDraft')
                                        ->downloadable()
                                        ->openable()
                                        ->hidden(function($state){
                                            if($state){
                                                return false;
                                            }
                                            else {
                                                return true;
                                            }
                                        })
                                        ->required()
                                        ->label('Initial Draft'),
                                    Forms\Components\FileUpload::make('finalDraft')
                                        ->downloadable()
                                        ->openable()
                                        ->hidden(function($state){
                                            if($state){
                                                return false;
                                            }
                                            else {
                                                return true;
                                            }
                                        })
                                        ->required()
                                        ->label('Final Draft'),
                                    Forms\Components\FileUpload::make('signedCopy')
                                        ->downloadable()
                                        ->openable()
                                        ->hidden(function($state){
                                            if($state){
                                                return false;
                                            }
                                            else {
                                                return true;
                                            }
                                        })
                                        ->required()
                                        ->label('Signed Copy'),                        
                                ]),
                            Section::make()
                                ->schema([
                                    Repeater::make('actionTaken')
                                        ->schema([
                                            Textarea::make('taken_action')
                                                ->label(' ')
                                                ->maxLength(500)
                                                ->required(),
                                        ])
                                        ->deletable(false)
                                        ->addable(false)
                                        ->columns(1)
                                ]),
                        ]),
                    Tables\Actions\EditAction::make()
                        // ->button()
                        ,
                    Tables\Actions\DeleteAction::make()
                        // ->button()
                        ,
                    Tables\Actions\Action::make('forward_divisionAcc')
                        ->icon('heroicon-o-chevron-double-right')
                        ->label('Forward')
                        ->hidden(function(Document $record){
                            if((Auth::user()->hasRole('records') || Auth::user()->hasRole('super_admin')) || (Auth::user()->hasRole('division_records') && $record->status != 'Received - Division')){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->color('success')
                        ->modalSubmitActionLabel('Forward')
                        // ->fillform()
                        ->form([
                            Section::make('Recipient')
                                ->schema([
                                    Select::make('division')
                                        ->label('Select Division')
                                        ->native(false)
                                        ->options([
                                            'OD' => 'Office of the Director',
                                            'PM' => 'PMTSSD',
                                            'PP' => 'PPDD',
                                            'LS' => 'LSRAD',
                                        ])
                                        ->required(),
                                    Select::make('user')
                                        ->label('Select User')
                                        ->native(false)
                                        ->options(function (Get $get) {
                                            if (!$get('division')) {
                                                return []; // Return an empty array if division is not selected
                                            }
                                            return User::query()
                                                ->where('division', $get('division'))
                                                ->get()
                                                ->mapWithKeys(function (User $user) {
                                                    return [$user->id => $user->firstname . ' ' . $user->lastname];
                                                });
                                        })
                                        ->required(),
                                ])
                                ->columns(2),
                            Section::make()
                                ->schema([
                                    Textarea::make('remarks')
                                        ->label('Remarks')
                                ])
                                ->columns(1),
                            
                        ])
                        ->action(function (array $data, Document $record): void {
                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuidFw = 'sent-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $forward= Forward::create([
                                'id'=> $uuidFw,
                                'documentId' => $record->rms_id,
                                'document_name' => $record->subject,
                                'document_description' => $record->description,
                                'document_type' => $record->docType,
                                'sender' => Auth::user()->id,
                                'sender_division' => Auth::user()->division,
                                'remarks' => $data['remarks'],
                                'receiver'=> $data['user'],
                                'receiver_division'=> $data['division'],
                                'status'=> 'Forwarded',
                            ]);
                            
                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Forwarded',
                                'recipient' => $data['user'],
                                'recipient_division' => $data['division'],
                            ]);
                            $record->holder_user = null;
                            $record->holder_division = null;
                            $record->status = 'Outgoing';
                            $record->forward_id = $uuidFw;
                            $record->save();
                        }),
                    Tables\Actions\Action::make('forward_release')
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->label('Forward to Recipient')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-arrow-down-on-square-stack')
                        ->modalIconColor('primary')
                        ->modalHeading('Release Document')
                        ->modalDescription('Are you sure you\'d like to release this document to the recipient?')
                        ->modalSubmitActionLabel('Affirmative')
                        // ->button()
                        ->hidden(function(Document $record){
                            if(Auth::user()->hasRole('division_record') && $record->status == 'Received - Division'){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->action(function(Document $record): void {
                            $forward = Forward::query()->where('id', $record->forward_id)->first();
                            $forward->status = 'Released';
                            $forward->save();
                            
                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Released',
                                'recipient' => $forward->receiver,
                                'recipient_division' => $forward->receiver_division,
                            ]);
                            $record->holder_user = null;
                            $record->holder_division = null;
                            $record->status = 'Outgoing';
                            $record->save();
                        }),
                    Tables\Actions\Action::make('forward_reroute')
                        ->icon('heroicon-o-user-circle')
                        ->label('Change Recipient')
                        ->color('info')
                        ->modalSubmitActionLabel('Reroute')
                        ->hidden(function(Document $record){
                            if(Auth::user()->hasRole('division_record') && $record->status == 'Received - Division'){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->form([
                            Select::make('user')
                                ->label('Select User')
                                ->native(false)
                                ->options(function (Get $get) {
                                    if (!$get('division')) {
                                        return []; // Return an empty array if division is not selected
                                    }
                            
                                    return User::query()
                                        ->where('division', Auth::user()->division)
                                        ->get()
                                        ->mapWithKeys(function (User $user) {
                                            return [$user->id => $user->firstname . ' ' . $user->lastname];
                                        });
                                })
                                ->required(),
                        ])
                        ->action(function(Document $record): void {
                            $forward = Forward::query()->where('id', $record->forward_id)->first();
                            $forward->receiver = $data['user'];
                            $forward->save();

                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Released',
                                'recipient' => $forward->receiver,
                                'recipient_division' => $forward->receiver_division,
                            ]);
                        }),
                    Tables\Actions\Action::make('update')
                        ->icon('heroicon-o-pencil-square')
                        ->label('Update Document')
                        ->color('info')
                        ->modalSubmitActionLabel('Update')
                        ->hidden(function(Document $record){
                            if(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief') || Auth::user()->hasRole('records')){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->fillform(
                            function (Document $record): array {
                                return [
                                    'subject' => $record->subject ,
                                    'description' => $record->description ,
                                    'docType' => $record->docType ,
                                    'initialDraft' => $record->initialDraft ,
                                    'finalDraft' => $record->finalDraft ,
                                    'signedCopy' => $record->signedCopy ,
                                    'actionTaken' => $record->actionTaken,
                                ];
                            }
                        )
                        ->form([
                            Section::make()
                                ->columns(2)
                                ->schema([
                                    Section::make()
                                        ->columns(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('subject')
                                                ->label('Subject')
                                                ->disabled()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('docType')
                                                ->label('Document Type')
                                                ->disabled()
                                                ->maxLength(255),
                                            Forms\Components\Textarea::make('description')
                                                ->label('Description')
                                                ->disabled()
                                                ->columnSpan(2),
                                        ]),
                                    Section::make()
                                        ->schema([
                                            Select::make('selectUpload')
                                                ->label('Select File Type')
                                                ->native(false)
                                                ->options([
                                                    'initial' => 'Initial Draft',
                                                    'final' => 'Final Draft',
                                                    'signed' => 'Signed Copy',
                                                ])
                                                ->required()
                                                ->live(),                    
                                            Forms\Components\FileUpload::make('initialDraft')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function(Get $get){
                                                    if($get('selectUpload')=='initial'){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Initial Draft'),
                                            Forms\Components\FileUpload::make('finalDraft')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function(Get $get){
                                                    if($get('selectUpload')=='final'){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Final Draft'),
                                            Forms\Components\FileUpload::make('signedCopy')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function(Get $get){
                                                    if($get('selectUpload')=='signed'){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Signed Copy'),
                                        ])
                                        ->columns(2),
                                                            
                                ]),
                        ])
                        ->action(function(array $data, Document $record): void {
                            // dd($data);
                            $record->initialDraft = isset($data['initialDraft']) ? $data['initialDraft'] : null;
                            $record->finalDraft = isset($data['finalDraft']) ? $data['finalDraft'] : null;
                            $record->signedCopy = isset($data['signedCopy']) ? $data['signedCopy'] : null;
                            $record->save();

                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Updated',
                                'recipient' => 'N/A',
                                'recipient_division' => 'N/A',
                            ]);
                        }),
                    Tables\Actions\Action::make('forward_emp')
                        ->icon('heroicon-o-chevron-double-right')
                        ->label('Route the Document')
                        ->hidden(function(Document $record){
                            if(Auth::user()->hasRole('employee') || Auth::user()->hasRole('division_chief')){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->color('success')
                        ->modalSubmitActionLabel('Submit')
                        ->steps([
                            Step::make('Define Action Taken')
                                ->schema([
                                    Repeater::make('actionTaken')
                                        ->schema([
                                            Textarea::make('taken_action')
                                                ->label(' ')
                                                ->maxLength(500)
                                                ->required(),
                                        ])
                                        ->addActionLabel('Add Another Action Taken')
                                        ->columns(1)
                                ]),
                            Step::make('Select Recipient')
                                ->schema([
                                    Section::make('Recipient')
                                        ->schema([
                                            Select::make('division')
                                                ->label('Select Division')
                                                ->native(false)
                                                ->options([
                                                    'OD' => 'Office of the Director',
                                                    'PM' => 'PMTSSD',
                                                    'PP' => 'PPDD',
                                                    'LS' => 'LSRAD',
                                                ])
                                                ->required(),
                                            Select::make('user')
                                                ->label('Select User')
                                                ->native(false)
                                                ->options(function (Get $get) {
                                                    if (!$get('division')) {
                                                        return []; // Return an empty array if division is not selected
                                                    }
                                            
                                                    return User::query()
                                                        ->where('division', $get('division'))
                                                        ->get()
                                                        ->mapWithKeys(function (User $user) {
                                                            return [$user->id => $user->firstname . ' ' . $user->lastname];
                                                        });
                                                })
                                                ->required(),
                                        ])
                                        ->columns(2),
                                    Section::make()
                                        ->schema([
                                            Textarea::make('remarks')
                                                ->label('Remarks')
                                        ])
                                        ->columns(1),
                                ]),
                            
                            
                        ])
                        ->action(function (array $data, Document $record): void {

                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuidFw = 'sent-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $forward= Forward::create([
                                'id'=> $uuidFw,
                                'documentId' => $record->rms_id,
                                'document_name' => $record->subject,
                                'document_description' => $record->description,
                                'document_type' => $record->docType,
                                'sender' => Auth::user()->id,
                                'sender_division' => Auth::user()->division,
                                'remarks' => $data['remarks'],
                                'receiver'=> $data['user'],
                                'receiver_division'=> Auth::user()->division,
                                'receiver_divisionTemp' => $data['division'],
                                'status'=> 'Routed',
                            ]);
                            
                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Routed',
                                'recipient' => $data['user'],
                                'recipient_division' => $data['division'],
                            ]);

                            $record->actionTaken = $data['actionTaken'];
                            $record->holder_user = null;
                            $record->holder_division = null;
                            $record->status = 'Routed';
                            $record->forward_id = $uuidFw;
                            $record->save();
                        }),
                    Tables\Actions\Action::make('route_toDiv')
                        ->icon('heroicon-o-forward')
                        ->label('Route Document')
                        ->disabledForm()
                        ->color('success')
                        ->modalSubmitActionLabel('Route')
                        ->fillform(function (Document $record): array {
                            $forward = Forward::query()->where('id', $record->forward_id)->first();
                            $user = User::where('id', $forward->sender)->first();
                            $sender = $user->firstname. ' ' .$user->lastname;
                            $user = User::where('id', $forward->receiver)->first();
                            $receiver = $user->firstname. ' ' .$user->lastname;
                            return [
                                'subject' => $record->subject,
                                'docType' => $record->docType,
                                'description' => $record->description,
                                'initialDraft' => $record->initialDraft,
                                'finalDraft' => $record->finalDraft,
                                'signedCopy' => $record->signedCopy,
                                'actionTaken' => $record->actionTaken,
                                'sender' => $sender,
                                'sender_division' => $forward->sender_division,
                                'remarks' => $forward->remarks,
                                'receiver' => $receiver,
                                'receiver_divisionTemp' => $forward->receiver_division,
                            ];
                        })
                        ->form([
                            Section::make('Document Details')
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
                                            Forms\Components\FileUpload::make('initialDraft')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function($state){
                                                    if($state){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Initial Draft'),
                                            Forms\Components\FileUpload::make('finalDraft')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function($state){
                                                    if($state){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Final Draft'),
                                            Forms\Components\FileUpload::make('signedCopy')
                                                ->downloadable()
                                                ->openable()
                                                ->hidden(function($state){
                                                    if($state){
                                                        return false;
                                                    }
                                                    else {
                                                        return true;
                                                    }
                                                })
                                                ->required()
                                                ->label('Signed Copy'),                        
                                        ]),
                                    Section::make()
                                        ->schema([
                                            Repeater::make('actionTaken')
                                                ->schema([
                                                    Textarea::make('taken_action')
                                                        ->label(' ')
                                                        ->maxLength(500)
                                                        ->required(),
                                                ])
                                                ->addable(false)
                                                ->columns(1)
                                        ]),
                            ]),
                            Section::make('Sender')
                                ->schema([
                                    TextInput::make('sender')
                                        ->label('Sender')
                                        ->readOnly(),
                                    Textarea::make('remarks')
                                        ->label('Remarks')
                                        ->readOnly(),
                                ])
                                ->columns(2),
                            Section::make('Receiver')
                                ->schema([
                                    TextInput::make('receiver')
                                        ->label('Receiver')
                                        ->readOnly(),
                                    TextInput::make('receiver_divisionTemp')
                                        ->label('Division')
                                        ->readOnly(),
                                ])
                                ->columns(2),
                        ])
                        ->hidden(function($record){
                            $res = (Auth::user()->hasRole('division_record') && $record->status == 'Received - Internal');
                            if($res){
                                return false;
                            }else{
                                return true;
                            }
                        })
                        ->action(function(Document $record): void {
                            Notification::make()
                                ->title('Working')
                                ->icon('heroicon-o-document-text')
                                ->iconColor('success')
                                ->send();
                            $forward = Forward::query()->where('id', $record->forward_id)->first();
                            $forward->status = 'Forwarded';
                            $forward->receiver_divisionTemp = NULL;
                            $forward->save();
                            
                            $uuid = Uuid::uuid4()->toString();
                            $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
                            $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
                            $log=Log::create([
                                'id' => $uuid,
                                'docId' => $record->rms_id,
                                'doc_name' => $record->subject,
                                'doc_description' => $record->description,
                                'doc_type' => $record->docType,
                                'user' => Auth::user()->id,
                                'user_division' => Auth::user()->division,
                                'transaction' => 'Forwarded',
                                'recipient' => $forward->receiver,
                                'recipient_division' => $forward->receiver_division,
                            ]);
                            $record->holder_user = null;
                            $record->holder_division = null;
                            $record->status = 'Forwarded';
                            $record->save();
                        }),
                ])->dropdown(),

                
                    
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            
            ->emptyStateHeading('You Have No Active Documents')
            ->emptyStateDescription('Check the Incoming Tab.')
            ->emptyStateIcon('heroicon-o-document-text');;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
