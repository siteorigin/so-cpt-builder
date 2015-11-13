module.exports = {
    slug: 'so-cpt-builder',
    jsMinSuffix: '.min',
    version: {
        src: [
            'so-cpt-builder.php',
            'readme.txt'
        ]
    },
    less: {
        src:['css/**/*.less'],
        include:[]
    },
    js: {
        src: [
            'js/**/*.js',
            '!{build,build/**}',                // Ignore build/ and contents
            '!{tmp,tmp/**}'                     // Ignore dist/ and contents
        ]
    },
    copy: {
        src: [
            '**/!(*.js|*.less)',                // Everything except .js and .less files
            '!{build,build/**}',                // Ignore build/ and contents
            '!{tmp,tmp/**}',                    // Ignore tmp/ and contents
            '!so-cpt-builder.php',              // Not the base plugin file. It is copied by the 'version' task.
            '!readme.txt'                       // Not the readme.txt file. It is copied by the 'version' task.
        ]
    }
};