<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

  //  protected static ?string $activeNavigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static ?string $navigationGroup = 'Admin Panel';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'status';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor():?string
    {
        return 'info';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number'];
    }

    public static function getGlobalSearchResultDetails(Model $record):array
    {
        return ['Customer' => $record->customer->name];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Order Details')
                        ->schema([
                            Forms\Components\TextInput::make('number')
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->default('OR-'. random_int(100000, 1000000)),

                            Forms\Components\Select::make('customer_id')
                                ->required()
                                ->relationship('customer', 'name')
                                ->preload()
                                ->searchable(),

                            Forms\Components\TextInput::make('shipping_price')
                                ->required()
                                ->label('Shipping Costs')
                                ->numeric()
                                ->dehydrated()
                                ->prefix('$'),

                            Forms\Components\Select::make('status')
                                ->required()
                                ->options([
                                    'processing' => OrderStatusEnum::PROCESSING->value,
                                    'completed' => OrderStatusEnum::COMPLETED->value,
                                    'pending' => OrderStatusEnum::PENDING->value,
                                    'declined' => OrderStatusEnum::DECLINED->value,
                                ]),

                            Forms\Components\MarkdownEditor::make('notes')
                                ->columnSpanFull()
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->required()
                                        ->label('Product')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Forms\Set $set) =>
                                            $set('unit_price', Product::find($state)?->price ?? 0)),

                                    Forms\Components\TextInput::make('quantity')
                                        ->required()
                                        ->numeric()
                                        ->live()
                                        ->dehydrated()
                                        ->default(1),

                                    Forms\Components\TextInput::make('unit_price')
                                        ->required()
                                        ->disabled()
                                        ->label('Unit Price')
                                        ->numeric()
                                        ->dehydrated()
                                        ->prefix('$'),

                                    Forms\Components\Placeholder::make('total_price')
                                        ->label('Total Price')
                                        ->content(function ($get){
                                            return '$'.$get('quantity') * $get('unit_price');
                                        })

                                ])->columns(4),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
