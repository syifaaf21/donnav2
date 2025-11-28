/** @type {import('tailwindcss').Config} */
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{html,js}",
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    '-apple-system',           // iPhone, iPad, Mac → San Francisco
                    'BlinkMacSystemFont',      // Safari Mac fallback
                    '"Segoe UI"',              // Windows
                    'Roboto',                  // Android
                    'Oxygen',
                    'Ubuntu',
                    'Cantarell',
                    '"Helvetica Neue"',
                    'sans-serif',
                ],
            },
            colors: {
                primary: "#4569AD", // blue
                primaryDark: "#2C4A7B", // dark blue
                danger: "#FF6F61", // red
                dangerDark: "#CC5649", // dark red
                warning: "#FFA500", // orange
                warningDark: "#CC8400", // dark orange
                success: "#28A745", // green
                successDark: "#1E7E34", // dark green
                accent: "#00C853",  // energetic green
                sidebar: "#1F3F74", // dark blue
                sidebar2: "#f7f3ec",
                matcha2: "#BED3CC", // light yellow
                matcha: "#CADDE1",
                yale: "#2f4156", // deep blue
                bgLight: "#f8efe5", // light blue
            },
        },
    },
    plugins: [],
}

