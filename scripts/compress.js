const {createReadStream, createWriteStream} = require('fs');
const {createGzip, createBrotliCompress} = require('zlib');

/**
 * Method that will create a gzipped vestion of the given file
 *
 * @param   { string }  file  The path of the file
 *
 * @returns { void }
 */
const gzipFile = (file) => {
  // eslint-disable-next-line no-console
  console.log(`Compressing: ${file}`);

  const fileContents = createReadStream(file);
  const writeStreamGz = createWriteStream(
    file.replace(/\.js$/, '.js.gz').replace(/\.css$/, '.css.gz')
  );
  const writeStreamBr = createWriteStream(
    file.replace(/\.js$/, '.js.br').replace(/\.css$/, '.css.br')
  );

  fileContents.pipe(createGzip()).pipe(writeStreamGz);
  fileContents.pipe(createBrotliCompress()).pipe(writeStreamBr);
};

module.exports.gzipFile = gzipFile;
