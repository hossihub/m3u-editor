<?php

namespace App\Filament\Tables;

use App\Models\Series;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecordableSeriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Series::query())
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->where('enabled', true)
                    ->where('user_id', auth()->id())
                    ->with(['playlist', 'category']);
            })
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
            ->paginated([15, 25, 50, 100])
            ->defaultPaginationPageOption(15)
            ->columns([
                TextColumn::make('name')
                    ->label('Series')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('playlist.name')
                    ->label('Playlist')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('episodes_count')
                    ->label('Episodes')
                    ->counts('episodes')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('playlist_id')
                    ->label('Playlist')
                    ->relationship('playlist', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} [{$record->playlist->name}]")
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('name');
    }
}
