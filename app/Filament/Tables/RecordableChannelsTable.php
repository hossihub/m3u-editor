<?php

namespace App\Filament\Tables;

use App\Models\Channel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecordableChannelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                // $arguments = $table->getArguments();

                return $query->where('user_id', auth()->id());
            })
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
            ->query(
                Channel::query()
                    ->where('enabled', true)
                    ->with(['playlist', 'group'])
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Channel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('playlist.name')
                    ->label('Playlist')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group.name')
                    ->label('Group')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stream_icon')
                    ->label('Logo')
                    ->formatStateUsing(fn ($state) => $state ? '✓' : '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('playlist_id')
                    ->label('Playlist')
                    ->relationship('playlist', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} [{$record->playlist->name}]")
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('title');
    }
}
