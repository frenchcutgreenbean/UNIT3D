<form class="form" method="POST"action="{{ $group->exists
        ? route('staff.groups.update', ['group' => $group->id])
        : route('staff.groups.store') }}">
    @csrf
    @if($group->exists)
        @method('PATCH')
    @endif
    <p class="form__group">
        <input id="name" class="form__text" type="text" name="group[name]" placeholder=" "
            value="{{ $group->name ?? '' }}" />
        <label class="form__label form__label--floating" for="name">
            {{ __('common.name') }}
        </label>
    </p>
    <p class="form__group">
        <input id="description" class="form__text" type="text" name="group[description]" placeholder=" "
            value="{{ $group->description ?? '' }}" />
        <label class="form__label form__label--floating" for="description">
            {{ __('common.description') }}
        </label>
    </p>
    <p class="form__group">
        <input id="position" class="form__text" type="text" name="group[position]" placeholder=" "
            value="{{ $group->position ?? '' }}" />
        <label class="form__label form__label--floating" for="position">
            {{ __('common.position') }}
        </label>
    </p>
    <p class="form__group">
        <input id="level" class="form__text" type="text" name="group[level]" placeholder=" "
            value="{{ $group->level ?? '' }}" />
        <label class="form__label form__label--floating" for="level">Level</label>
    </p>
    <p class="form__group">
        <input id="download_slots" class="form__text" type="text" name="group[download_slots]" placeholder=" "
            value="{{ $group->download_slots ?? '' }}" />
        <label class="form__label form__label--floating" for="download_slots">
            DL Slots
        </label>
    </p>
    <p class="form__group">
        <input id="color" class="form__text" type="text" name="group[color]" placeholder=" "
            value="{{ $group->color ?? '' }}" />
        <label class="form__label form__label--floating" for="color">
            Color (e.g. #ff0000)
        </label>
    </p>
    <p class="form__group">
        <input id="icon" class="form__text" type="text" name="group[icon]" placeholder=" "
            value="{{ $group->icon ?? '' }}" />
        <label class="form__label form__label--floating" for="icon">
            FontAwesome Icon (e.g. fas fa-user)
        </label>
    </p>
    <p class="form__group">
        <input id="effect" class="form__text" type="text" name="group[effect]" placeholder="GIF Effect"
            value="{{ $group->effect ?? '' }}" />
        <label class="form__label form__label--floating" for="effect">
            Effect (e.g. url(/img/sparkels.gif))
        </label>
    </p>
    @foreach (\App\Models\Group::flagFields() as $field => $label)
        <p class="form__group">
            <input name="group[{{ $field }}]" type="hidden" value="0" />
            <input id="{{ $field }}" class="form__checkbox" name="group[{{ $field }}]" type="checkbox"
                value="1" @checked(old("group.$field", $group->$field ?? false)) />
            <label class="form__label" for="{{ $field }}">{{ $label }}</label>
        </p>
    @endforeach

    <div class="form__group" x-show="autogroup">
        <fieldset class="form form__fieldset">
            <legend class="form__legend">Autogroup requirements</legend>
            <p class="form__group">
                <input id="min_uploaded" class="form__text" type="text" name="group[min_uploaded]" placeholder=" "
                    value="{{ $group->min_uploaded ?? '' }}" />
                <label class="form__label form__label--floating" for="min_uploaded">
                    Minimum upload
                </label>
            </p>
            <p class="form__group">
                <input id="min_ratio" class="form__text" type="text" name="group[min_ratio]" placeholder=" "
                    value="{{ $group->min_ratio ?? '' }}" />
                <label class="form__label form__label--floating" for="min_ratio">
                    Minimum ratio
                </label>
            </p>
            <p class="form__group">
                <input id="min_age" class="form__text" type="text" name="group[min_age]" placeholder=" "
                    value="{{ $group->min_age ?? '' }}" />
                <label class="form__label form__label--floating" for="min_age">
                    Minimum age
                </label>
            </p>
            <p class="form__group">
                <input id="min_avg_seedtime" class="form__text" type="text" name="group[min_avg_seedtime]"
                    placeholder=" " value="{{ $group->min_avg_seedtime ?? '' }}" />
                <label class="form__label form__label--floating" for="min_avg_seedtime">
                    Minimum average seedtime
                </label>
            </p>
            <p class="form__group">
                <input id="min_seedsize" class="form__text" type="text" name="group[min_seedsize]"
                    placeholder=" " value="{{ $group->min_seedsize ?? '' }}" />
                <label class="form__label form__label--floating" for="min_seedsize">
                    Minimum seedsize
                </label>
            </p>
            <p class="form__group">
                <input id="min_uploads" class="form__text" type="text" name="group[min_uploads]" placeholder=" "
                    value="{{ $group->min_uploads ?? '' }}" />
                <label class="form__label form__label--floating" for="min_uploads">
                    Minimum uploads
                </label>
            </p>
        </fieldset>
    </div>
    <div class="form__group">
        <label class="form__label">Permissions</label>
        <div class="data-table-wrapper">
            <table class="data-table data-table--checkbox-grid" x-data="checkboxGrid">
                <thead>
                    <tr>
                        <th x-bind="columnHeader">Forum Category</th>
                        <th x-bind="columnHeader">Forum</th>
                        <th x-bind="columnHeader">Read topics</th>
                        <th x-bind="columnHeader">Start new topic</th>
                        <th x-bind="columnHeader">Reply to topics</th>
                    </tr>
                </thead>
                <tbody x-ref="tbody">
                    @foreach ($forumCategories as $forumCategory)
                        @foreach ($forumCategory->forums as $forum)
                            <tr>
                                @if ($loop->first)
                                    <th rowspan="{{ $forumCategory->forums->count() }}" x-bind="rowHeader">
                                        {{ $forum->category->name }}
                                    </th>
                                @else
                                    <th style="display: none"></th>
                                @endif

                                <th x-bind="rowHeader">
                                    {{ $forum->name }}
                                    <input type="hidden" name="permissions[{{ $forum->id }}][forum_id]"
                                        value="{{ $forum->id }}" />
                                </th>
                                <td x-bind="cell">
                                    <input type="hidden" name="permissions[{{ $forum->id }}][read_topic]"
                                        value="0" />
                                    <input type="checkbox" name="permissions[{{ $forum->id }}][read_topic]"
                                        value="1" @checked($group->permissions->where('forum_id', '=', $forum->id)->first()?->read_topic) />
                                </td>
                                <td x-bind="cell">
                                    <input type="hidden" name="permissions[{{ $forum->id }}][start_topic]"
                                        value="0" />
                                    <input type="checkbox" name="permissions[{{ $forum->id }}][start_topic]"
                                        value="1" @checked($group->permissions->where('forum_id', '=', $forum->id)->first()?->start_topic) />
                                </td>
                                <td x-bind="cell">
                                    <input type="hidden" name="permissions[{{ $forum->id }}][reply_topic]"
                                        value="0" />
                                    <input type="checkbox" name="permissions[{{ $forum->id }}][reply_topic]"
                                        value="1" @checked($group->permissions->where('forum_id', '=', $forum->id)->first()?->reply_topic) />
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <p class="form__group">
        <button class="form__button form__button--filled">
            {{ __('common.submit') }}
        </button>
    </p>
</form>
