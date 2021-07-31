
addEventListener('fetch', event => {
    event.respondWith(
      handleRequest(event.request).catch(err => {
        const message = JSON.stringify(err.reason || err.stack || 'Unknown Error', null, 2)
  
        return new Response(message, {
          status: err.status || 500,
          statusText: err.statusText || null,
          headers: {
            'Content-Type': 'application/json;charset=UTF-8',
            // Disables caching by default.
            'Cache-Control': 'no-store',
            // Returns the "Content-Length" header for HTTP HEAD requests.
            'Content-Length': message.length,
          }
        })
      })
    )
  })
  
  async function handleRequest(request) {
    const { protocol, pathname } = new URL(request.url)
  
    // In the case of a "Basic" authentication, the exchange 
    // MUST happen over an HTTPS (TLS) connection to be secure.
    if ('https:' !== protocol || 'https' !== request.headers.get('x-forwarded-proto')) {
      throw new BadRequestException('Please use a HTTPS connection.')
    }
    if (!request.headers.has('Authorization')) {
      return new Response('You need to login.', {
          status: 401,
          headers: {
            // Prompts the user for credentials.
            'WWW-Authenticate': 'Basic realm="ACF Composer Repository", charset="UTF-8"'
          }
        })
    }
    const { user, pass } = basicAuthentication(request);
    verifyCredentials(user, pass);
  
    let url = null;
  
    switch(pathname) {
      case "/wordpress-plugin/packages.json":
        url = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-plugin/packages.json";
        break;
      case "/wordpress-muplugin/packages.json":
        url = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-muplugin/packages.json"
        break;
      case "/library/packages.json":
        url = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/library/packages.json"
        break;
      case "/wpackagist-plugin/packages.json":
        url = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wpackagist-plugin/packages.json"
        break;
      default:
        throw new NotFoundRequestException("Unknown repository");
    }
    
    const init = {
      headers: {
        "content-type": "application/json;charset=UTF-8",
      },
    }
  
    const response = await fetch(url, init);
    
    let json = await response.json();
    json = {
        ...json,
        packages: {
          ...json.packages,
          "advanced-custom-fields/advanced-custom-fields-pro": objectMap(json.packages['advanced-custom-fields/advanced-custom-fields-pro'], p => {
          const oldUrl = new URL(p.dist.url);
          const newUrl = new URL("https://auth-acf-composer-proxy.pivvenit.net/download");
          newUrl.searchParams.set('t', oldUrl.searchParams.get('t'));
          return {...p, dist: {...p.dist, url: newUrl.toString()}, require: {"composer/installers": "~1.0"}};
        })
        }
    };
    
    const newResponse = new Response(JSON.stringify(json, null, 2), response);
    newResponse.headers.set("Cache-Control", "no-store")
    return newResponse;
  }
  
  /**
   * Throws exception on verification failure.
   * @param {string} user
   * @param {string} pass
   * @throws {UnauthorizedException}
   */
  function verifyCredentials(user, pass) {
    if ("licensekey" !== user) {
      throw new UnauthorizedException('Invalid username, please use licensekey as username.')
    }
  }
  
  /**
   * Parse HTTP Basic Authorization value.
   * @param {Request} request
   * @throws {BadRequestException}
   * @returns {{ user: string, pass: string }}
   */
  function basicAuthentication(request) {
    const Authorization = request.headers.get('Authorization')
  
    const [scheme, encoded] = Authorization.split(' ')
  
    // The Authorization header must start with "Basic", followed by a space.
    if (!encoded || scheme !== 'Basic') {
      throw new BadRequestException('Malformed authorization header.')
    }
  
    // Decodes the base64 value and performs unicode normalization.
    // @see https://datatracker.ietf.org/doc/html/rfc7613#section-3.3.2 (and #section-4.2.2)
    // @see https://dev.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/String/normalize
    const decoded = atob(encoded).normalize()
    
    // The username & password are split by the first colon.
    //=> example: "username:password"
    const index = decoded.indexOf(':')
  
    // The user & password are split by the first colon and MUST NOT contain control characters.
    // @see https://tools.ietf.org/html/rfc5234#appendix-B.1 (=> "CTL = %x00-1F / %x7F")
    if (index === -1 || /[\0-\x1F\x7F]/.test(decoded)) {
      throw new BadRequestException('Invalid authorization value.')
    }
    
    return { 
      user: decoded.substring(0, index),
      pass: decoded.substring(index + 1),
    }
  }
  
  function UnauthorizedException(reason) {
    this.status = 401
    this.statusText = 'Unauthorized'
    this.reason = reason
  }
  
  function BadRequestException(reason) {
    this.status = 400
    this.statusText = 'Bad Request'
    this.reason = reason
  }
  
  function NotFoundRequestException(reason) {
    this.status = 404
    this.statusText = 'Not found'
    this.reason = reason
  }
  
  
  const objectMap = (obj, fn) =>
    Object.fromEntries(
      Object.entries(obj).map(
        ([k, v], i) => [k, fn(v, k, i)]
      )
    )
  