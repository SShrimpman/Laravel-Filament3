<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\RelationManagers\AuthorsRelationManager;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Tabs::make('Create New Post')->tabs([
                    Tab::make('Tab 1')
                        ->icon('heroicon-m-inbox')
                        ->iconPosition(IconPosition::After)
                        ->badge('Hi')
                        ->schema([
                            TextInput::make('title')->required(),
                            TextInput::make('slug')->minLength(3)->maxLength(10)->unique(ignoreRecord:true)->required(),
                            Select::make('category_id')
                                ->label('Category')
                                // ->options(Category::all()->pluck('name', 'id'))
                                ->relationship('category', 'name')
                                ->searchable()
                                ->required(),
                            ColorPicker::make('color')->required(),
                        ]),
                    Tab::make('Content')->schema([
                        MarkdownEditor::make('content')->required()->columnSpan('full'),
                    ]),
                    Tab::make('Meta')->schema([
                        FileUpload::make('thumbnail')->disk('public')->directory('thumbnails')->nullable(),
                        TagsInput::make('tags')->required(),
                        Checkbox::make('published'),
                    ]),
                ])->activeTab(3)->persistTabInQueryString(),

                // Section::make('Create a Post')
                //     ->description('create posts over here.')
                //     // ->aside() com isto eu separo a descrição que está em cima e meto ao lado, separada do schema
                //     // ->collapsed() Com isto posso colapsar a imagem, para vicar visível ou não
                //     ->schema([
                //         Group::make()->schema([
                //             TextInput::make('title')->required(),
                //             TextInput::make('slug')->required(),
                //         ]),

                //         // nas rules posso usar duas formas : em string => rules('min:3|max:10'), em array => rules(['min:3', 'max:10'])
                //         TextInput::make('title')->numeric()->minValue(3)->maxValue(10)->required(),
                //         TextInput::make('title')->required(),
                //         TextInput::make('slug')->minLength(3)->maxLength(10)->unique(ignoreRecord:true)->required(),
                //         Select::make('category_id')
                //             ->label('Category')
                //             // ->options(Category::all()->pluck('name', 'id'))
                //             ->relationship('category', 'name')
                //             ->searchable()
                //             ->required(),
                //         ColorPicker::make('color')->required(),
                //         MarkdownEditor::make('content')->required()->columnSpan('full'), // posso user columnSpanFull() ou columnSpan('full') para ocupar as colunas todas em width
                //     ])->columnSpan(2)->columns(2),
            //     Group::make()
            //         ->schema([
            //             Section::make('Image')
            //                 ->collapsed()
            //                 ->schema([
            //                     FileUpload::make('thumbnail')->disk('public')->directory('thumbnails')->nullable(),
            //                 ])->columnSpan(1),
            //             Section::make('Meta')->schema([
            //                 TagsInput::make('tags')->required(),
            //                 Checkbox::make('published'),
            //             ]),
            //             // Section::make('Authors')->schema([
            //             //     // Select::make('authors')
            //             //     //     ->label('Co Authors')
            //             //     //     ->multiple()
            //             //     //     ->relationship('authors', 'name')
            //             //     CheckboxList::make('authors')
            //             //         ->searchable()
            //             //         ->label('Co Authors')
            //             //         ->relationship('authors', 'name')
            //             // ])
            //         ])
            ])->columns(1);
            // ->columns([ isto é para eu controlar a responsividade, similarmente parecido com o TailwindCSS, normalmente não é preciso porque o Filament é bastante inteligente
            //     'default' => 1,
            //     'md' => 2,
            //     'lg' => 3,
            //     'xl' => 4,
            // ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable()->toggleable(isToggledHiddenByDefault:true),
                ImageColumn::make('thumbnail')->toggleable(),
                ColorColumn::make('color')->toggleable(),
                TextColumn::make('title')->sortable()->searchable()->toggleable(),
                TextColumn::make('slug')->sortable()->searchable()->toggleable(),
                TextColumn::make('category.name')->sortable()->searchable()->toggleable(),
                TextColumn::make('tags')->toggleable(),
                CheckboxColumn::make('published')->toggleable(),
                TextColumn::make('created_at')->label('Published on')->date()->sortable()->searchable()->toggleable(),
            ])
            ->filters([
                // Filter::make('Published Posts')->query(
                //     function (Builder $query) : Builder { // este Builder é para o meu intelephense detetar as funções do PHP e puder utilizá-las com a variável
                //         return $query->where('published', true);
                //     }
                // ),
                // Filter::make('Published Posts')->query(
                //     function (Builder $query) : Builder {
                //         return $query->where('published', false);
                //     }
                // ),
                TernaryFilter::make('published'),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    // ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    // ->multiple()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AuthorsRelationManager::class,
            CommentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
