/**
 * Parse all source JS language files (JSON pulled from Transifex) from client/src/lang
 * to SilverStripe i18n js file in client/lang
 * Quick and dirty node script!
 */
var fs = require('fs');
var path = require('path');

const PATHS = {
  SRC: path.resolve('client/src/lang'),
  DIST: path.resolve('client/lang'),
};

console.log('Writing SS i18n JS lang files...');

fs.readdir(PATHS.SRC, function(err, files)
{
    files.forEach(function (file) {
        var lang = file.split('.').shift();
        fs.readFile(PATHS.SRC + '/' + file, "utf8", function(err, data) {
            if (err) { console.log(err); }
            var fileData = `if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
  if(typeof(console) != 'undefined') console.error('Class ss.i18n not defined');
} else {
  ss.i18n.addDictionary('${lang}', ${data});
}`;

            fs.writeFile(PATHS.DIST + '/' + lang + '.js', fileData, "utf8", function(err) {
                if (err) { console.log(err); }
                console.log("Saved " + lang + '.js');
            }); 
        });
    });
});