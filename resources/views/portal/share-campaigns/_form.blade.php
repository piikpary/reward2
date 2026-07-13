@php
    $editing = isset($shareCampaign);
@endphp

<div class="campaign-card">
    <div class="form-grid">
        {{-- Business ID --}}
        <div class="field">
            <label for="business_id">
                Business ID
            </label>

            <input
                id="business_id"
                type="number"
                min="1"
                name="business_id"
                value="{{ old(
                    'business_id',
                    $editing
                        ? $shareCampaign->business_id
                        : ''
                ) }}"
            >

            <div class="help">
                Leave blank to use the logged-in
                portal user's business.
            </div>

            @error('business_id')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Campaign title --}}
        <div class="field">
            <label for="title">
                Campaign title
            </label>

            <input
                id="title"
                type="text"
                name="title"
                maxlength="255"
                required
                value="{{ old(
                    'title',
                    $editing
                        ? $shareCampaign->title
                        : ''
                ) }}"
            >

            @error('title')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Description --}}
        <div class="field field-full">
            <label for="description">
                Campaign description
            </label>

            <textarea
                id="description"
                name="description"
                maxlength="5000"
                placeholder="Enter the campaign description"
            >{{ old(
                'description',
                $editing
                    ? $shareCampaign->description
                    : ''
            ) }}</textarea>

            @error('description')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Campaign poster --}}
        <div class="field">
            <label for="image">
                Campaign poster
            </label>

            <input
                id="image"
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
                @required(!$editing)
            >

            <div class="help">
                Accepted formats: JPG, JPEG, PNG, and WebP.
                Maximum size: 5 MB.
            </div>

            @if ($editing)
                <div class="help">
                    Leave this field empty to keep the
                    current campaign poster.
                </div>
            @endif

            @error('image')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Current poster preview --}}
        <div class="field">
            @if (
                $editing
                && $shareCampaign->imageUrl()
            )
                <label>
                    Current poster
                </label>

                <img
                    class="poster-large"
                    src="{{ $shareCampaign->imageUrl() }}"
                    alt="{{ $shareCampaign->title }}"
                >
            @else
                <label>
                    Poster preview
                </label>

                <div class="help">
                    The campaign poster preview will appear
                    after the campaign is saved.
                </div>
            @endif
        </div>

        {{-- Share URL --}}
        <div class="field field-full">
            <label for="share_url">
                Campaign share URL
            </label>

            <input
                id="share_url"
                type="url"
                name="share_url"
                maxlength="2048"
                placeholder="https://example.com/campaign/..."
                value="{{ old(
                    'share_url',
                    $editing
                        ? $shareCampaign->share_url
                        : ''
                ) }}"
            >

            <div class="help">
                Optional. When this field is empty, the API
                uses APP_URL/campaign/{campaignId}.
            </div>

            @error('share_url')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Required shares --}}
        <div class="field">
            <label for="required_shares">
                Required shares
            </label>

            <input
                id="required_shares"
                type="number"
                min="1"
                max="1000000"
                name="required_shares"
                required
                value="{{ old(
                    'required_shares',
                    $editing
                        ? $shareCampaign->required_shares
                        : 1
                ) }}"
            >

            <div class="help">
                Number of verified shares required
                before spins are awarded.
            </div>

            @error('required_shares')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Reward spins --}}
        <div class="field">
            <label for="reward_spins">
                Reward spins
            </label>

            <input
                id="reward_spins"
                type="number"
                min="1"
                max="1000000"
                name="reward_spins"
                required
                value="{{ old(
                    'reward_spins',
                    $editing
                        ? $shareCampaign->reward_spins
                        : 1
                ) }}"
            >

            <div class="help">
                Number of spins awarded when the
                customer reaches the required shares.
            </div>

            @error('reward_spins')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Start date --}}
        <div class="field">
            <label for="starts_at">
                Start date and time
            </label>

            <input
                id="starts_at"
                type="datetime-local"
                name="starts_at"
                value="{{ old(
                    'starts_at',
                    $editing
                    && $shareCampaign->starts_at
                        ? $shareCampaign
                            ->starts_at
                            ->format('Y-m-d\TH:i')
                        : ''
                ) }}"
            >

            <div class="help">
                Leave blank to start the campaign immediately.
            </div>

            @error('starts_at')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Expiry date --}}
        <div class="field">
            <label for="expires_at">
                Expiry date and time
            </label>

            <input
                id="expires_at"
                type="datetime-local"
                name="expires_at"
                value="{{ old(
                    'expires_at',
                    $editing
                    && $shareCampaign->expires_at
                        ? $shareCampaign
                            ->expires_at
                            ->format('Y-m-d\TH:i')
                        : ''
                ) }}"
            >

            <div class="help">
                Leave blank when the campaign has no expiry date.
            </div>

            @error('expires_at')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Campaign settings --}}
        <div class="field field-full">
            <label>
                Campaign settings
            </label>

            <div class="checkbox-row">
                <div class="checkbox-item">
                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >

                    <input
                        id="is_active"
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(
                            old(
                                'is_active',
                                $editing
                                    ? $shareCampaign->is_active
                                    : true
                            )
                        )
                    >

                    <label for="is_active">
                        Active campaign
                    </label>
                </div>

                <div class="checkbox-item">
                    <input
                        type="hidden"
                        name="is_published"
                        value="0"
                    >

                    <input
                        id="is_published"
                        type="checkbox"
                        name="is_published"
                        value="1"
                        @checked(
                            old(
                                'is_published',
                                $editing
                                    ? $shareCampaign->is_published
                                    : false
                            )
                        )
                    >

                    <label for="is_published">
                        Publish campaign
                    </label>
                </div>

                <div class="checkbox-item">
                    <input
                        type="hidden"
                        name="reward_repeatable"
                        value="0"
                    >

                    <input
                        id="reward_repeatable"
                        type="checkbox"
                        name="reward_repeatable"
                        value="1"
                        @checked(
                            old(
                                'reward_repeatable',
                                $editing
                                    ? $shareCampaign->reward_repeatable
                                    : false
                            )
                        )
                    >

                    <label for="reward_repeatable">
                        Repeat reward at every milestone
                    </label>
                </div>
            </div>

            <div class="help">
                Example: if the requirement is 100 shares and
                repeat reward is enabled, rewards are awarded at
                100, 200, 300, and later milestones.
            </div>
        </div>
    </div>

    <div class="form-footer">
        <a
            class="button button-secondary"
            href="{{ route(
                'portal.share-campaigns.index'
            ) }}"
        >
            Cancel
        </a>

        <button
            class="button button-primary"
            type="submit"
        >
            {{
                $editing
                    ? 'Update Campaign'
                    : 'Create Campaign'
            }}
        </button>
    </div>
</div>