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
                inter: ['Inter', 'sans-serif'],
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
                bgLight: "#8EA2D7", // light blue
            },
        },
    },
    plugins: [],
}

