<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                Section::make('Create a Post')
                    ->description('create posts over here.')
                    // ->aside() com isto eu separo a descrição que está em cima e meto ao lado, separada do schema
                    // ->collapsed() Com isto posso colapsar a imagem, para vicar visível ou não
                    ->schema([
                        // Group::make()->schema([
                        //     TextInput::make('title')->required(),
                        //     TextInput::make('slug')->required(),
                        // ]),

                        // nas rules posso usar duas formas : em string => rules('min:3|max:10'), em array => rules(['min:3', 'max:10'])
                        TextInput::make('title')->numeric()->minValue(3)->maxValue(10)->required(),
                        TextInput::make('slug')->minLength(3)->maxLength(10)->unique(ignoreRecord:true)->required(),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),
                        ColorPicker::make('color')->required(),
                        MarkdownEditor::make('content')->required()->columnSpan('full'), // posso user columnSpanFull() ou columnSpan('full') para ocupar as colunas todas em width
                    ])->columnSpan(2)->columns(2),
                Group::make()
                    ->schema([
                        Section::make('Image')
                            ->collapsed()
                            ->schema([
                                FileUpload::make('thumbnail')->disk('public')->directory('thumbnails')->nullable(),
                            ])->columnSpan(1),
                        Section::make('Meta')->schema([
                            TagsInput::make('tags')->required(),
                            Checkbox::make('published'),
                        ])
                    ])
            ])->columns(3);
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
                ImageColumn::make('thumbnail'),
                ColorColumn::make('color'),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                TextColumn::make('category.name'),
                TextColumn::make('tags'),
                CheckboxColumn::make('published')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
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
