<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $documentationTitle }}</title>

  {{-- Swagger UI (CDN) --}}
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>

  <style>
    html { box-sizing: border-box; overflow-y: scroll; }
    *, *::before, *::after { box-sizing: inherit; }
    body { margin: 0; background: #fafafa; }
  </style>

  @if (config('l5-swagger.defaults.ui.display.dark_mode'))
    <style>
      body#dark-mode, #dark-mode .scheme-container { background: #1b1b1b; }
      #dark-mode .scheme-container, #dark-mode .opblock .opblock-section-header { box-shadow: 0 1px 2px 0 rgba(255,255,255,.15); }
      #dark-mode .operation-filter-input, #dark-mode .dialog-ux .modal-ux,
      #dark-mode input[type=email], #dark-mode input[type=file], #dark-mode input[type=password],
      #dark-mode input[type=search], #dark-mode input[type=text], #dark-mode textarea { background:#343434; color:#e7e7e7; }
      #dark-mode .title, #dark-mode li, #dark-mode p, #dark-mode table, #dark-mode label, #dark-mode .opblock-tag,
      #dark-mode .opblock .opblock-summary-operation-id, #dark-mode .opblock .opblock-summary-path,
      #dark-mode .opblock .opblock-summary-path__deprecated, #dark-mode h1, #dark-mode h2, #dark-mode h3, #dark-mode h4, #dark-mode h5,
      #dark-mode .btn, #dark-mode .tab li, #dark-mode .parameter__name, #dark-mode .parameter__type,
      #dark-mode .prop-format, #dark-mode .loading-container .loading:after { color:#e7e7e7; }
      #dark-mode .opblock-description-wrapper p, #dark-mode .opblock-external-docs-wrapper p, #dark-mode .opblock-title_normal p,
      #dark-mode .response-col_status, #dark-mode table thead tr td, #dark-mode table thead tr th,
      #dark-mode .response-col_links, #dark-mode .swagger-ui { color:wheat; }
      #dark-mode .parameter__extension, #dark-mode .parameter__in, #dark-mode .model-title { color:#949494; }
      #dark-mode table thead tr td, #dark-mode table thead tr th { border-color: rgba(120,120,120,.2); }
      #dark-mode .opblock .opblock-section-header { background: transparent; }
      #dark-mode .opblock.opblock-post   { background: rgba(73,204,144,.25); }
      #dark-mode .opblock.opblock-get    { background: rgba(97,175,254,.25); }
      #dark-mode .opblock.opblock-put    { background: rgba(252,161,48,.25); }
      #dark-mode .opblock.opblock-delete { background: rgba(249,62,62,.25); }
      #dark-mode .loading-container .loading:before { border-color: rgba(255,255,255,10%); border-top-color: rgba(255,255,255,.6); }
      #dark-mode svg:not(:root){ fill:#e7e7e7; }
      #dark-mode .opblock-summary-description { color:#fafafa; }
    </style>
  @endif
</head>

@php
  // Gera caminhos RELATIVOS (sem host/porta)
  $docsJson = config('l5-swagger.documentations.default.paths.docs_json', 'api-docs.json');
  // ATENÇÃO: aqui usamos o nome da rota (que você acabou de mudar para 'swagger')
  $docsPath = route('l5-swagger.default.docs', [], false) . '?' . $docsJson; // "/swagger?api-docs.json"
  $oauthCb  = route('l5-swagger.default.oauth2_callback', [], false);        // "/api/oauth2-callback"
@endphp

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
  <div id="swagger-ui"></div>

  <script>
    window.onload = function () {
      const ui = SwaggerUIBundle({
        dom_id: '#swagger-ui',

        // Caminho relativo → sem redirects/sem CORS
        url: @json($docsPath),

        operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
        configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
        validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},

        oauth2RedirectUrl: @json($oauthCb),

        requestInterceptor: function (request) {
          request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
          return request;
        },

        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        plugins: [SwaggerUIBundle.plugins.DownloadUrl],
        layout: 'StandaloneLayout',
        docExpansion: "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
        deepLinking: true,
        filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
        persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",
      });

      window.ui = ui;

      @if (in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
      ui.initOAuth({
        usePkceWithAuthorizationCodeGrant: "{!! (bool) config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
      });
      @endif
    };
  </script>
</body>
</html>
