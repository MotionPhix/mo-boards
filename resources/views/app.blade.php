<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to apply theme immediately to prevent flash --}}
        <script>
            (function() {
                // Get theme from cookie (set by our theme system)
                const getCookie = (name) => {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                    return null;
                };

                const theme = getCookie('theme') || 'system';

                const applyTheme = (mode) => {
                    if (mode === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else if (mode === 'light') {
                        document.documentElement.classList.remove('dark');
                    } else {
                        // system mode
                        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                        if (prefersDark) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                };

                applyTheme(theme);

                // Listen for system preference changes if using system theme
                if (theme === 'system') {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                        applyTheme('system');
                    });
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @php($isTesting = app()->environment('testing'))
        @unless($isTesting)
            @vite(['resources/js/app.ts'])
        @endunless
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
