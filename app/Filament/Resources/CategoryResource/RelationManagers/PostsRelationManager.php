<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

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
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
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
                    // TextInput::make('title')->numeric()->minValue(3)->maxValue(10)->required(),
                    TextInput::make('title')->required(),
                    TextInput::make('slug')->minLength(3)->maxLength(10)->unique(ignoreRecord:true)->required(),
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
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\CheckboxColumn::make('published'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
