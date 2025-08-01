<?php
session_start();
require_once '../includes/functions.php';

$currentPage = '404';
$pageTitle = '404 - Page Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - 404 Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }

        /* 404 TV Design with Black and White Filter */
        .main_wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30em;
            height: 30em;
            filter: grayscale(100%);
        }

        .main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 5em;
        }

        .antenna {
            width: 5em;
            height: 5em;
            border-radius: 50%;
            border: 2px solid black;
            background-color: #f27405;
            margin-bottom: -6em;
            margin-left: 0em;
            z-index: -1;
        }
        .antenna_shadow {
            position: absolute;
            background-color: transparent;
            width: 50px;
            height: 56px;
            margin-left: 1.68em;
            border-radius: 45%;
            transform: rotate(140deg);
            border: 4px solid transparent;
            box-shadow:
                inset 0px 16px #a85103,
                inset 0px 16px 1px 1px #a85103;
            -moz-box-shadow:
                inset 0px 16px #a85103,
                inset 0px 16px 1px 1px #a85103;
        }
        .antenna::after {
            content: "";
            position: absolute;
            margin-top: -9.4em;
            margin-left: 0.4em;
            transform: rotate(-25deg);
            width: 1em;
            height: 0.5em;
            border-radius: 50%;
            background-color: #f69e50;
        }
        .antenna::before {
            content: "";
            position: absolute;
            margin-top: 0.2em;
            margin-left: 1.25em;
            transform: rotate(-20deg);
            width: 1.5em;
            height: 0.8em;
            border-radius: 50%;
            background-color: #f69e50;
        }
        .a1 {
            position: relative;
            top: -102%;
            left: -130%;
            width: 12em;
            height: 5.5em;
            border-radius: 50px;
            background-image: linear-gradient(
                #171717,
                #171717,
                #353535,
                #353535,
                #171717
            );
            transform: rotate(-29deg);
            clip-path: polygon(50% 0%, 49% 100%, 52% 100%);
        }
        .a1d {
            position: relative;
            top: -211%;
            left: -35%;
            transform: rotate(45deg);
            width: 0.5em;
            height: 0.5em;
            border-radius: 50%;
            border: 2px solid black;
            background-color: #979797;
            z-index: 99;
        }
        .a2 {
            position: relative;
            top: -210%;
            left: -10%;
            width: 12em;
            height: 4em;
            border-radius: 50px;
            background-color: #171717;
            background-image: linear-gradient(
                #171717,
                #171717,
                #353535,
                #353535,
                #171717
            );
            margin-right: 5em;
            clip-path: polygon(
                47% 0,
                47% 0,
                34% 34%,
                54% 25%,
                32% 100%,
                29% 96%,
                49% 32%,
                30% 38%
            );
            transform: rotate(-8deg);
        }
        .a2d {
            position: relative;
            top: -294%;
            left: 94%;
            width: 0.5em;
            height: 0.5em;
            border-radius: 50%;
            border: 2px solid black;
            background-color: #979797;
            z-index: 99;
        }

        .notfound_text {
            background-color: black;
            padding-left: 0.3em;
            padding-right: 0.3em;
            font-size: 0.75em;
            color: white;
            letter-spacing: 0;
            border-radius: 5px;
            z-index: 10;
        }
        .tv {
            width: 17em;
            height: 9em;
            margin-top: 3em;
            border-radius: 15px;
            background-color: #d36604;
            display: flex;
            justify-content: center;
            border: 2px solid #1d0e01;
            box-shadow: inset 0.2em 0.2em #e69635;
        }
        .tv::after {
            content: "";
            position: absolute;
            width: 17em;
            height: 9em;
            border-radius: 15px;
            background:
                repeating-radial-gradient(#d36604 0 0.0001%, #00000070 0 0.0002%) 50% 0/2500px
                  2500px,
                repeating-conic-gradient(#d36604 0 0.0001%, #00000070 0 0.0002%) 60% 60%/2500px
                  2500px;
            background-blend-mode: difference;
            opacity: 0.09;
        }
        .curve_svg {
            position: absolute;
            margin-top: 0.25em;
            margin-left: -0.25em;
            height: 12px;
            width: 12px;
        }
        .display_div {
            display: flex;
            align-items: center;
            align-self: center;
            justify-content: center;
            border-radius: 15px;
            box-shadow: 3.5px 3.5px 0px #e69635;
        }
        .screen_out {
            width: auto;
            height: auto;
            border-radius: 10px;
        }
        .screen_out1 {
            width: 11em;
            height: 7.75em;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .screen {
            width: 13em;
            height: 7.85em;
            font-family: Montserrat;
            border: 2px solid #1d0e01;
            background:
                repeating-radial-gradient(#000 0 0.0001%, #ffffff 0 0.0002%) 50% 0/2500px
                  2500px,
                repeating-conic-gradient(#000 0 0.0001%, #ffffff 0 0.0002%) 60% 60%/2500px
                  2500px;
            background-blend-mode: difference;
            animation: b 0.2s infinite alternate;
            border-radius: 10px;
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #252525;
            letter-spacing: 0.15em;
            text-align: center;
        }

        .screenM {
            width: 13em;
            height: 7.85em;
            position: relative;
            font-family: Montserrat;
            background: linear-gradient(
                to right,
                #002fc6 0%,
                #002bb2 14.2857142857%,
                #3a3a3a 14.2857142857%,
                #303030 28.5714285714%,
                #ff0afe 28.5714285714%,
                #f500f4 42.8571428571%,
                #6c6c6c 42.8571428571%,
                #626262 57.1428571429%,
                #0affd9 57.1428571429%,
                #00f5ce 71.4285714286%,
                #3a3a3a 71.4285714286%,
                #303030 85.7142857143%,
                white 85.7142857143%,
                #fafafa 100%
            );
            border-radius: 10px;
            border: 2px solid black;
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #252525;
            letter-spacing: 0.15em;
            text-align: center;
            overflow: hidden;
        }
        .screenM:before,
        .screenM:after {
            content: "";
            position: absolute;
            left: 0;
            z-index: 1;
            width: 100%;
        }
        .screenM:before {
            top: 0;
            height: 68.4782608696%;
            background: linear-gradient(
                to right,
                white 0%,
                #fafafa 14.2857142857%,
                #ffe60a 14.2857142857%,
                #f5dc00 28.5714285714%,
                #0affd9 28.5714285714%,
                #00f5ce 42.8571428571%,
                #10ea00 42.8571428571%,
                #0ed600 57.1428571429%,
                #ff0afe 57.1428571429%,
                #f500f4 71.4285714286%,
                #ed0014 71.4285714286%,
                #d90012 85.7142857143%,
                #002fc6 85.7142857143%,
                #002bb2 100%
            );
        }
        .screenM:after {
            bottom: 0;
            height: 21.7391304348%;
            background: linear-gradient(
                to right,
                #006c6b 0%,
                #005857 16.6666666667%,
                white 16.6666666667%,
                #fafafa 33.3333333333%,
                #001b75 33.3333333333%,
                #001761 50%,
                #6c6c6c 50%,
                #626262 66.6666666667%,
                #929292 66.6666666667%,
                #888888 83.3333333333%,
                #3a3a3a 83.3333333333%,
                #303030 100%
            );
        }

        @keyframes b {
            100% {
                background-position:
                    50% 0,
                    60% 50%;
            }
        }

        .lines {
            display: flex;
            column-gap: 0.1em;
            align-self: flex-end;
        }
        .line1,
        .line3 {
            width: 2px;
            height: 0.5em;
            background-color: black;
            border-radius: 25px 25px 0px 0px;
            margin-top: 0.5em;
        }
        .line2 {
            flex-grow: 1;
            width: 2px;
            height: 1em;
            background-color: black;
            border-radius: 25px 25px 0px 0px;
        }

        .buttons_div {
            width: 4.25em;
            align-self: center;
            height: 8em;
            background-color: #e69635;
            border: 2px solid #1d0e01;
            padding: 0.6em;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            row-gap: 0.75em;
            box-shadow: 3px 3px 0px #e69635;
        }
        .b1 {
            width: 1.65em;
            height: 1.65em;
            border-radius: 50%;
            background-color: #7f5934;
            border: 2px solid black;
            box-shadow:
                inset 2px 2px 1px #b49577,
                -2px 0px #513721,
                -2px 0px 0px 1px black;
        }
        .b1::before {
            content: "";
            position: absolute;
            margin-top: 1em;
            margin-left: 0.5em;
            transform: rotate(47deg);
            border-radius: 5px;
            width: 0.1em;
            height: 0.4em;
            background-color: #000000;
        }
        .b1::after {
            content: "";
            position: absolute;
            margin-top: 0.9em;
            margin-left: 0.8em;
            transform: rotate(47deg);
            border-radius: 5px;
            width: 0.1em;
            height: 0.55em;
            background-color: #000000;
        }
        .b1 div {
            content: "";
            position: absolute;
            margin-top: -0.1em;
            margin-left: 0.65em;
            transform: rotate(45deg);
            width: 0.15em;
            height: 1.5em;
            background-color: #000000;
        }
        .b2 {
            width: 1.65em;
            height: 1.65em;
            border-radius: 50%;
            background-color: #7f5934;
            border: 2px solid black;
            box-shadow:
                inset 2px 2px 1px #b49577,
                -2px 0px #513721,
                -2px 0px 0px 1px black;
        }
        .b2::before {
            content: "";
            position: absolute;
            margin-top: 1.05em;
            margin-left: 0.8em;
            transform: rotate(-45deg);
            border-radius: 5px;
            width: 0.15em;
            height: 0.4em;
            background-color: #000000;
        }
        .b2::after {
            content: "";
            position: absolute;
            margin-top: -0.1em;
            margin-left: 0.65em;
            transform: rotate(-45deg);
            width: 0.15em;
            height: 1.5em;
            background-color: #000000;
        }
        .speakers {
            display: flex;
            flex-direction: column;
            row-gap: 0.5em;
        }
        .speakers .g1 {
            display: flex;
            column-gap: 0.25em;
        }
        .speakers .g1 .g11,
        .g12,
        .g13 {
            width: 0.65em;
            height: 0.65em;
            border-radius: 50%;
            background-color: #7f5934;
            border: 2px solid black;
            box-shadow: inset 1.25px 1.25px 1px #b49577;
        }
        .speakers .g {
            width: auto;
            height: 2px;
            background-color: #171717;
        }

        .bottom {
            width: 100%;
            height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            column-gap: 8.7em;
        }
        .base1 {
            height: 1em;
            width: 2em;
            border: 2px solid #171717;
            background-color: #4d4d4d;
            margin-top: -0.15em;
            z-index: -1;
        }
        .base2 {
            height: 1em;
            width: 2em;
            border: 2px solid #171717;
            background-color: #4d4d4d;
            margin-top: -0.15em;
            z-index: -1;
        }
        .base3 {
            position: absolute;
            height: 0.15em;
            width: 17.5em;
            background-color: #171717;
            margin-top: 0.8em;
        }

        .text_404 {
            position: absolute;
            display: flex;
            flex-direction: row;
            column-gap: 6em;
            z-index: -5;
            margin-bottom: 2em;
            align-items: center;
            justify-content: center;
            opacity: 0.5;
            font-family: Montserrat;
        }
        .text_4041 {
            transform: scaleY(24.5) scaleX(9);
        }
        .text_4042 {
            transform: scaleY(24.5) scaleX(9);
        }
        .text_4043 {
            transform: scaleY(24.5) scaleX(9);
        }

        @media only screen and (max-width: 495px) {
            .text_404 {
                column-gap: 6em;
            }
        }
        @media only screen and (max-width: 395px) {
            .text_404 {
                column-gap: 4em;
            }
            .text_4041 {
                transform: scaleY(25) scaleX(8);
            }
            .text_4042 {
                transform: scaleY(25) scaleX(8);
            }
            .text_4043 {
                transform: scaleY(25) scaleX(8);
            }
        }

        @media (max-width: 275px), (max-height: 520px) {
            .main {
                position: relative;
            }
        }

        @media only screen and (max-width: 1024px) {
            .screenM {
                display: flex;
            }
            .screen {
                display: none;
            }
        }
        @media only screen and (min-width: 1025px) {
            .screen {
                display: flex;
            }
            .screenM {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Content Area -->
            <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- 404 Content -->
                <div class="flex flex-col items-center justify-center min-h-[calc(100vh-200px)]">
                    <div class="text-center mb-8">
                        <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-4">Page Not Found</h1>
                        <p class="text-lg text-gray-600 mb-8">The page you're looking for doesn't exist.</p>
                        <a href="index.php" class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200 inline-flex items-center">
                            <i class="fas fa-home mr-2"></i>
                            Go to Dashboard
                        </a>
                    </div>
                    
                    <!-- TV Animation -->
                    <div class="main_wrapper">
                        <div class="main">
                            <div class="antenna">
                                <div class="antenna_shadow"></div>
                                <div class="a1"></div>
                                <div class="a1d"></div>
                                <div class="a2"></div>
                                <div class="a2d"></div>
                                <div class="a_base"></div>
                            </div>
                            <div class="tv">
                                <div class="cruve">
                                    <svg
                                        class="curve_svg"
                                        version="1.1"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 189.929 189.929"
                                        xml:space="preserve"
                                    >
                                        <path
                                            d="M70.343,70.343c-30.554,30.553-44.806,72.7-39.102,115.635l-29.738,3.951C-5.442,137.659,11.917,86.34,49.129,49.13
                                        C86.34,11.918,137.664-5.445,189.928,1.502l-3.95,29.738C143.041,25.54,100.895,39.789,70.343,70.343z"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="display_div">
                                    <div class="screen_out">
                                        <div class="screen_out1">
                                            <div class="screen">
                                                <span class="notfound_text"> NOT FOUND</span>
                                            </div>
                                            <div class="screenM">
                                                <span class="notfound_text"> NOT FOUND</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="lines">
                                    <div class="line1"></div>
                                    <div class="line2"></div>
                                    <div class="line3"></div>
                                </div>
                                <div class="buttons_div">
                                    <div class="b1"><div></div></div>
                                    <div class="b2"></div>
                                    <div class="speakers">
                                        <div class="g1">
                                            <div class="g11"></div>
                                            <div class="g12"></div>
                                            <div class="g13"></div>
                                        </div>
                                        <div class="g"></div>
                                        <div class="g"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bottom">
                                <div class="base1"></div>
                                <div class="base2"></div>
                                <div class="base3"></div>
                            </div>
                        </div>
                        <div class="text_404">
                            <div class="text_4041">4</div>
                            <div class="text_4042">0</div>
                            <div class="text_4043">4</div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) { // Only on mobile
                    const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
        });
    </script>
</body>
</html> 