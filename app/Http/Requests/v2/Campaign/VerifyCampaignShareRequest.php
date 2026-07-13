<?php

namespace App\Http\Requests\v2\Campaign;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class VerifyCampaignShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => [
                'required',
                'integer',
                'exists:share_campaigns,id',
            ],

            'facebook_post_url' => [
                'bail',
                'required',
                'string',
                'max:2048',
                'url:http,https',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ): void {
                    $host = strtolower(
                        (string) parse_url(
                            $value,
                            PHP_URL_HOST
                        )
                    );

                    $host = rtrim($host, '.');

                    $validFacebookDomain =
                        $host === 'facebook.com'
                        || str_ends_with(
                            $host,
                            '.facebook.com'
                        );

                    if (!$validFacebookDomain) {
                        $fail(
                            'The URL must be a valid Facebook URL.'
                        );

                        return;
                    }

                    $path = trim(
                        (string) parse_url(
                            $value,
                            PHP_URL_PATH
                        ),
                        '/'
                    );

                    if ($path === '') {
                        $fail(
                            'The Facebook post URL is incomplete.'
                        );
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.required' =>
                'Campaign ID is required.',

            'campaign_id.exists' =>
                'Campaign does not exist.',

            'facebook_post_url.required' =>
                'Facebook post URL is required.',

            'facebook_post_url.url' =>
                'Facebook post URL is invalid.',
        ];
    }
}