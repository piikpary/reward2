<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title }}</title>

    <!-- Open Graph Meta Tags -->
    <meta
        property="og:url"
        content="{{ $pageUrl }}"
    >

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="{{ $title }}"
    >

    <meta
        property="og:description"
        content="{{ $description }}"
    >

    @if ($imageUrl)
        <meta
            property="og:image"
            content="{{ $imageUrl }}"
        >

        <meta
            property="og:image:secure_url"
            content="{{ $imageUrl }}"
        >

        <meta
            property="og:image:alt"
            content="{{ $title }}"
        >
    @endif

    <meta
        property="fb:app_id"
        content="{{ config('services.facebook.app_id') }}"
    >

    <!-- Twitter Card Meta Tags -->
    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="{{ $title }}"
    >

    <meta
        name="twitter:description"
        content="{{ $description }}"
    >

    @if ($imageUrl)
        <meta
            name="twitter:image"
            content="{{ $imageUrl }}"
        >
    @endif

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #172033;
        }

        .campaign-card {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            padding: 24px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .campaign-title {
            margin: 0 0 12px;
            font-size: 30px;
        }

        .campaign-description {
            margin: 0 0 20px;
            line-height: 1.6;
            color: #566176;
        }

        .campaign-image {
            display: block;
            width: 100%;
            max-height: 450px;
            object-fit: contain;
            margin-bottom: 24px;
            border-radius: 12px;
            background: #f5f5f5;
        }

        .open-app-button {
            display: inline-block;
            padding: 12px 20px;
            background: #0d1c2c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <main class="campaign-card">
        <h1 class="campaign-title">
            {{ $title }}
        </h1>

        <p class="campaign-description">
            {{ $description }}
        </p>

        @if ($imageUrl)
            <img
                class="campaign-image"
                src="{{ $imageUrl }}"
                alt="{{ $title }}"
            >
        @endif

        @if (!empty($appDeepLink))
            <a
                class="open-app-button"
                href="{{ $appDeepLink }}"
            >
                Open in ScanPrize app
            </a>
        @endif
    </main>
</body>
</html>