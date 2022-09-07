// Sync object
/** @type {import('@jest/types').Config.InitialOptions} */
module.exports = async () => {
    return {
        verbose: true,
        moduleFileExtensions: [
            "js",
            "json",
            "vue"
        ],
        moduleNameMapper: {
            "^@/(.*)$": "<rootDir>/resources/js/components/$1"
        },
        testEnvironment: "jest-environment-jsdom",
        transform: {
            "^.+\\.vue$": "@vue/vue2-jest",
            '^.+\\.js$': 'babel-jest',
        },
        collectCoverage: true,
        collectCoverageFrom: [
            "**/resources/js/components/*.{js,vue}",
            "!**/node_modules/**",
            "!**/vendor/**",
            "!**/public/**",
            "!**/coverage/**"
        ]
    };
};
