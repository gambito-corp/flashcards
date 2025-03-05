<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Correo')</title>
    <style>
        /* Estilos básicos para el email */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #51545e;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 20px;
        }
        .email-content {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .email-header, .email-footer {
            background-color: #3869d4;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .email-body {
            padding: 30px;
        }
        h1, h2, h3, h4 {
            margin-top: 0;
        }
        p {
            line-height: 1.5;
            margin: 0 0 20px;
        }
        a {
            color: #3869d4;
            text-decoration: none;
        }
        .button {
            background-color: #3869d4;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 3px;
            text-decoration: none;
            display: inline-block;
        }
        .site-logo a>div{
            width: 65px;
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-content">
        <div class="email-header">
            <div class="shrink-0 flex mr-24 pr-24 site-logo">
                <a href="{{ route('dashboard') }}">
                    <div class="w-[75px] h-[75px]">
                        <svg
                            class="w-full h-full overflow-visible"
                            viewBox="0 0 300 200"
                            preserveAspectRatio="xMidYMid meet"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Aquí va el contenido completo del SVG -->
                            <g xmlns="http://www.w3.org/2000/svg" id="g8" transform="matrix(1.3333333,0,0,-1.3333333,0,199.58267)">
                                <g id="g10" transform="translate(308.4697,124.1287)">
                                    <path d="m 0,0 h 3.618 l 16.025,-38.195 h 0.115 L 35.61,0 h 3.619 V -41.01 H 36.701 V -3.101 H 36.587 L 20.964,-41.01 H 18.437 L 2.642,-3.101 H 2.526 V -41.01 H 0 Z"
                                          style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                          id="path12"/>
                                </g>
                                <g id="g14" transform="translate(382.793,85.3016)">
                                    <path
                                        d="m 0,0 v -2.183 h -27.856 v 41.01 H -0.287 V 36.645 H -25.329 V 20.217 H -1.78 v -2.182 h -23.549 l 0,-18.035 z"
                                        style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                        id="path16" />
                                </g>
                                <g id="g18">
                                    <g id="g20" clip-path="url(#clipPath24)">
                                        <g id="g26" transform="translate(389.6289,85.3016)">
                                            <path
                                                d="m 0,0 h 11.027 c 7.64,0 16.313,4.193 16.313,18.38 0,16.771 -10.74,18.265 -16.829,18.265 L 0,36.645 Z m -2.527,38.827 h 13.44 c 14.646,0 18.954,-10.052 18.954,-20.447 0,-11.602 -5.916,-20.563 -19.069,-20.563 H -2.527 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path28" />
                                        </g>
                                        <g id="g30" transform="translate(443.9082,85.3016)">
                                            <path
                                                d="m 0,0 h 13.899 c 7.237,0 11.775,3.217 11.775,9.305 0,7.007 -6.375,8.73 -11.775,8.73 H 0 Z m 0,20.217 h 13.899 c 6.835,0 10.396,3.447 10.396,8.444 0,5.916 -4.94,7.984 -10.396,7.984 H 0 Z m -2.527,18.61 h 16.426 c 9.765,0 12.924,-5.341 12.924,-10.625 0,-4.423 -3.218,-8.156 -7.984,-8.731 l 0.114,-0.115 c 1.092,0.173 9.248,-1.78 9.248,-10.051 0,-7.467 -5.743,-11.488 -14.302,-11.488 H -2.527 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path32" />
                                        </g>
                                        <g id="g34" transform="translate(505.3076,124.1287)">
                                            <path
                                                d="M 0,0 H 2.814 L -13.67,-23.836 V -41.01 h -2.526 v 17.174 L -32.566,0 h 2.872 l 14.818,-21.71 z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path36" />
                                        </g>
                                        <g id="g38" transform="translate(338.9658,52.7576)">
                                            <path
                                                d="m 0,0 c -0.33,8.063 -6.411,12.096 -15.136,12.096 -5.354,0 -12.955,-2.248 -12.955,-10.047 0,-7.601 8.064,-8.858 16.062,-10.509 8.064,-1.652 16.128,-3.702 16.128,-13.088 0,-9.782 -9.386,-13.285 -16.59,-13.285 -11.038,0 -20.028,4.561 -19.897,16.722 h 2.909 c -0.595,-10.311 7.667,-14.21 16.988,-14.21 5.683,0 13.681,2.577 13.681,10.773 0,7.997 -8.063,9.386 -16.128,11.038 -7.997,1.653 -16.061,3.57 -16.061,12.559 0,9.055 8.395,12.558 15.863,12.558 9.849,0 17.582,-4.23 18.044,-14.607 z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path40" />
                                        </g>
                                        <g id="g42" transform="translate(344.7187,63.7293)">
                                            <path
                                                d="M 0,0 V 2.512 H 36.154 V 0 H 19.498 V -44.682 H 16.59 L 16.59,0 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path44" />
                                        </g>
                                        <g id="g46" transform="translate(387.0205,66.241)">
                                            <path
                                                d="m 0,0 v -28.289 c 0,-5.685 1.189,-17.516 14.807,-17.516 11.566,0 15.465,6.808 15.465,17.516 V 0 h 2.91 v -28.289 c 0,-11.236 -4.231,-20.027 -18.375,-20.027 -16.789,0 -17.715,13.748 -17.715,20.027 l 0,28.289 z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path48" />
                                        </g>
                                        <g id="g50" transform="translate(431.4385,21.5604)">
                                            <path
                                                d="m 0,0 h 12.69 c 8.791,0 18.773,4.824 18.773,21.15 0,19.3 -12.36,21.019 -19.367,21.019 H 0 Z M -2.908,44.681 H 12.559 C 29.413,44.681 34.37,33.113 34.37,21.15 34.37,7.8 27.562,-2.513 12.426,-2.513 H -2.908 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path52" />
                                        </g>
                                        <g id="g54" transform="translate(504.6738,21.5604)">
                                            <path
                                                d="M 0,0 V -2.513 H -32.056 V 44.681 H -0.33 V 42.169 H -29.148 V 23.266 h 27.1 v -2.513 h -27.1 V 0 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path56" />
                                        </g>
                                        <g id="g58" transform="translate(509.6338,66.241)">
                                            <path
                                                d="m 0,0 h 3.636 l 29.808,-43.293 h 0.133 V 0 h 2.908 V -47.193 H 33.049 L 3.041,-3.568 H 2.908 V -47.193 H 0 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path60" />
                                        </g>
                                        <g id="g62" transform="translate(549.3584,63.7293)">
                                            <path
                                                d="M 0,0 V 2.512 H 36.155 V 0 H 19.499 V -44.682 H 16.591 V 0 Z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path64" />
                                        </g>
                                        <g id="g66" transform="translate(619.5547,52.7576)">
                                            <path
                                                d="m 0,0 c -0.33,8.063 -6.411,12.096 -15.137,12.096 -5.353,0 -12.955,-2.248 -12.955,-10.047 0,-7.601 8.065,-8.858 16.062,-10.509 8.064,-1.652 16.129,-3.702 16.129,-13.088 0,-9.782 -9.387,-13.285 -16.591,-13.285 -11.038,0 -20.028,4.561 -19.896,16.722 h 2.909 c -0.595,-10.311 7.667,-14.21 16.987,-14.21 5.684,0 13.681,2.577 13.681,10.773 0,7.997 -8.063,9.386 -16.127,11.038 -7.998,1.653 -16.061,3.57 -16.061,12.559 0,9.055 8.395,12.558 15.862,12.558 9.849,0 17.583,-4.23 18.045,-14.607 z"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path68" />
                                        </g>
                                        <g id="g70" transform="translate(90.7803,112.2034)">
                                            <path
                                                d="m 0,0 -32.793,-36.9 -0.649,-0.732 c -2.539,-2.853 -6.006,-4.396 -9.54,-4.576 -0.217,-0.016 -0.434,-0.021 -0.65,-0.021 h -0.057 c -0.217,0 -0.434,0.005 -0.65,0.021 -3.535,0.18 -7.001,1.723 -9.535,4.576 l -0.639,0.717 L -87.322,0 c -5.025,5.655 -4.514,14.317 1.141,19.342 2.61,2.322 5.861,3.462 9.096,3.462 3.776,0 7.538,-1.553 10.246,-4.602 l 23.181,-26.08 23.176,26.08 c 2.709,3.049 6.469,4.602 10.246,4.602 3.235,0 6.485,-1.14 9.096,-3.462 C 4.515,14.317 5.025,5.655 0,0"
                                                style="fill:#157b80;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path72" />
                                        </g>
                                        <g id="g74" transform="translate(94.2197,121.6873)">
                                            <path
                                                d="m 0,0 c 0.095,-3.365 -1.028,-6.771 -3.439,-9.484 l -23.942,-26.94 v -53.793 c 0,-7.566 6.134,-13.7 13.7,-13.7 7.567,0 13.701,6.134 13.701,13.7 V -0.38 C 0.02,-0.251 0.004,-0.127 0,0"
                                                style="fill:#195b81;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path76" />
                                        </g>
                                        <g id="g78" transform="translate(3.4585,112.2034)">
                                            <path
                                                d="M 0,0 C -2.411,2.713 -3.534,6.119 -3.439,9.484 -3.443,9.356 -3.458,9.232 -3.458,9.104 v -89.837 c 0,-7.566 6.133,-13.7 13.7,-13.7 7.566,0 13.7,6.134 13.7,13.7 v 53.795 z"
                                                style="fill:#5b8080;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path80" />
                                        </g>
                                        <g id="g82" transform="translate(150.4316,95.4061)">
                                            <path
                                                d="M 0,0 C -1.834,-1.834 -4.352,-2.97 -7.144,-2.992 H -19.336 V 17.435 H -7.144 C -4.352,17.413 -1.834,16.277 0,14.443 1.849,12.594 2.992,10.04 2.992,7.225 2.992,4.403 1.849,1.849 0,0 m 11.713,37.635 c 0,0 0,0.005 -0.005,0.005 -3.339,1.268 -6.955,1.962 -10.736,1.962 H -26.8 c -0.755,0 -1.509,-0.028 -2.252,-0.082 -2.967,-0.219 -5.818,-0.863 -8.49,-1.88 -0.005,0 -0.011,-0.005 -0.017,-0.011 -3.9,-2.371 -6.506,-6.665 -6.506,-11.566 v -47.518 c 0,-0.377 0.016,-0.748 0.048,-1.114 -0.015,-0.011 -0.032,-0.023 -0.048,-0.034 v -8.76 c 1.198,0.676 17.87,9.909 32.335,8.419 9.743,-1.004 22.166,3.474 25.339,4.7 -0.01,0.004 -0.021,0.009 -0.033,0.013 1.257,0.568 2.464,1.229 3.623,1.966 1.879,1.197 3.622,2.596 5.191,4.163 5.485,5.481 8.873,13.054 8.873,21.418 0,12.944 -8.119,23.992 -19.55,28.319"
                                                style="fill:#195b81;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path84" />
                                        </g>
                                        <g id="g86" transform="translate(150.4316,42.0096)">
                                            <path
                                                d="M 0,0 C -1.834,-1.834 -4.352,-2.97 -7.144,-2.992 H -19.336 V 17.436 H -7.144 C -4.352,17.413 -1.834,16.276 0,14.442 1.849,12.594 2.992,10.039 2.992,7.225 2.992,4.403 1.849,1.849 0,0 m 19.125,33.883 -4.665,1.986 c 0,0 -14.852,-6.167 -26.112,-5.007 C -26.666,32.41 -44.063,22.4 -44.063,22.4 v -32.721 c 0,0 -2.54,-12.736 12.626,-13.25 15.166,-0.514 39.611,-0.514 39.611,-0.514 0,0 21.523,4.272 26.827,25.042 5.317,20.824 -15.876,32.926 -15.876,32.926"
                                                style="fill:#157b80;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path88" />
                                        </g>
                                        <g id="g90" transform="translate(223.0225,83.8836)">
                                            <path
                                                d="m 0,0 c -2.478,4.292 -3.188,7.836 -3.302,10.244 -3.825,2.496 -4.688,4.909 -4.688,7.835 0,4.609 4.1,8.71 12.338,8.71 6.458,0 10.999,-1.917 14.455,-5.296 4.209,-4.114 10.595,-5.015 15.676,-2.042 7.354,4.303 8.564,14.394 2.502,20.382 -8.394,8.295 -19.527,12.631 -32.633,12.631 -19.91,0 -38.662,-12.829 -38.662,-35.051 0,-22.048 16.79,-30.285 33.895,-35.053 4.657,-1.288 8.536,-2.433 11.721,-3.556 -0.206,2.709 -1.002,6.093 -3.096,9.804 -0.957,1.677 -2.178,3.418 -3.73,5.194 C 2.557,-4.003 1.102,-1.926 0,0"
                                                style="fill:#157b80;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path92" />
                                        </g>
                                        <g id="g94" transform="translate(235.7871,87.6434)">
                                            <path
                                                d="m 0,0 c -7.933,2.404 -12.903,4.451 -15.981,6.443 0.1,-2.387 0.79,-5.918 3.239,-10.203 1.093,-1.892 2.529,-3.931 4.41,-6.083 1.588,-1.816 2.83,-3.596 3.796,-5.308 2.118,-3.713 2.933,-7.104 3.153,-9.825 8.206,-2.902 11.767,-5.666 12.057,-10.409 0.016,-0.21 0.016,-0.437 0.016,-0.648 0,-4.451 -3.119,-9.376 -15.632,-9.376 -8.523,0 -14.501,2.763 -18.769,7.356 -4.108,4.423 -10.656,5.554 -15.878,2.526 l -1.129,-0.656 c -7.016,-4.068 -8.607,-13.541 -3.268,-19.646 8.258,-9.443 21.037,-15.256 38.22,-15.256 25.009,0 42.781,13.162 42.781,35.385 C 37.015,-11.356 17.438,-5.275 0,0"
                                                style="fill:#5b8080;fill-opacity:1;fill-rule:nonzero;stroke:none"
                                                id="path96" />
                                        </g>
                                        <g id="g98" transform="translate(289.4111,149.6873)">
                                            <path
                                                d="M 0,0 V -149.687"
                                                style="fill:#1d1d1b;fill-opacity:1;fill-rule:nonzero;stroke:#1d1d1b;stroke-width:0.963;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                id="path100" />
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>

                    </div>
                </a>
            </div>
            <h2>@yield('header', 'Mi Aplicación')</h2>
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Mi Aplicación. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
</body>
</html>
