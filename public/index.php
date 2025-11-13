<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReflectGit - El Arquitecto de tu Perfil</title>
    <link href="./css/style.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-between p-4 md:p-8">

    <header class="w-full max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-brand-accent">
            ReflectGit
        </h1>
    </header>

    <main class="w-full max-w-3xl mx-auto my-12" 
          x-data="{ 
              step: 1, 
              githubUser: '', 
              analysisResult: null,
              error: null,
              techLibrary: {},
              selectedTech: [],
              selectedTemplate: 'tecnologico',
              bioSuggestions: [], // <-- NUEVO
              selectedBio: '',   // <-- NUEVO
              bioLoading: false, // <-- NUEVO
              finalMarkdown: '',

              init() {
                  fetch('./obtener_tecnologias.php')
                      .then(response => response.json())
                      .then(data => { this.techLibrary = data; });
              },

              async analizarPerfil() {
                  this.step = 3; 
                  this.error = null;
                  try {
                      const response = await fetch(`./analizador.php?usuario=${this.githubUser}`);
                      const data = await response.json();
                      if (data.error) {
                          this.error = data.error;
                          this.step = 2; 
                      } else {
                          this.analysisResult = data;
                          this.preSeleccionarTecnologias();
                          this.step = 4;
                      }
                  } catch (e) {
                      this.error = 'No se pudo conectar con el servidor.';
                      this.step = 2;
                  }
              },

              preSeleccionarTecnologias() {
                  this.selectedTech = [];
                  if (!this.analysisResult || !this.analysisResult.lenguajes) return;
                  const lenguajesDetectados = Object.keys(this.analysisResult.lenguajes);
                  for (const categoria in this.techLibrary) {
                      for (const tech of this.techLibrary[categoria]) {
                          if (lenguajesDetectados.includes(tech.nombre)) {
                              this.selectedTech.push(tech.nombre);
                          }
                      }
                  }
              },
              
              // Visión Pro: 2. ¡NUEVA FUNCIÓN!
              // Llama a nuestro script de IA.
              async generarBios() {
                  this.step = 6; // Avanza al nuevo paso 6
                  this.bioLoading = true; // Muestra el 'spinner'
                  this.error = null;
                  
                  try {
                      const response = await fetch('./generador_bio.php', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/json' },
                          body: JSON.stringify({ tecnologias: this.selectedTech })
                      });
                      const data = await response.json();

                      if (data.error) {
                          this.error = data.error;
                      } else {
                          this.bioSuggestions = data.sugerencias;
                          this.selectedBio = data.sugerencias[0]; // Selecciona la primera por defecto
                      }
                  } catch (e) {
                      this.error = 'No se pudo contactar al motor de IA.';
                  } finally {
                      this.bioLoading = false; // Oculta el 'spinner'
                  }
              },

              // Visión Pro: 3. Actualizamos generarCodigo()
              // - Ahora también envía 'selectedBio'.
              // - Ahora se llama desde el PASO 7.
              async generarCodigo() {
                  this.step = 3; // Reusamos la pantalla de carga
                  this.error = null;

                  try {
                      const response = await fetch('./generador.php', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/json' },
                          body: JSON.stringify({
                              tecnologias: this.selectedTech,
                              usuario: this.githubUser,
                              plantilla: this.selectedTemplate,
                              biografia: this.selectedBio // <-- NUEVO DATO ENVIADO
                          })
                      });
                      const data = await response.json();

                      if (data.error) {
                          this.error = data.error;
                          this.step = 7; // Vuelve al paso de plantillas
                      } else {
                          this.finalMarkdown = data.markdown_final;
                          this.step = 8; // ¡Avanza al paso final (ahora 8)!
                      }
                  } catch (e) {
                      this.error = 'Error al generar el código.';
                      this.step = 7; // Vuelve al paso de plantillas
                  }
              }
          }">
        
        <div class="bg-brand-surface border border-brand-border rounded-lg shadow-2xl overflow-hidden">
            
            <div x-show="step === 1" class="p-6 md:p-10">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 1: ¡Bienvenido a ReflectGit!</h2>
                <p class="text-brand-text-dim">Vamos a analizar tu perfil de GitHub para sugerirte automáticamente las tecnologías que dominas.</p>
                <p class="text-brand-text-dim mt-4">Presiona "Siguiente" para empezar.</p>
            </div>
            
            <div x-show="step === 2" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-4">Paso 2: ¿Cuál es tu usuario de GitHub?</h2>
                <div x-show="error" class="mb-4 p-3 bg-red-800/20 border border-red-500/30 rounded-lg text-red-400" x-text="error" style="display: none;"></div>
                <label for="github_user" class="block text-sm font-medium text-brand-text-dim">Nombre de usuario</label>
                <div class="mt-1">
                    <input type="text" id="github_user" name="github_user" x-model="githubUser" @keydown.enter="analizarPerfil()" placeholder="ej: JohnManrique900" class="w-full px-4 py-3 bg-brand-bg border border-brand-border rounded-lg text-brand-text placeholder-brand-text-dim/50 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary">
                </div>
            </div>

            <div x-show="step === 3" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-4">Procesando...</h2>
                <p class="text-brand-text-dim">Generando tu perfil profesional...</p>
                <div class="w-full bg-brand-border/30 rounded-full h-2.5 mt-6">
                    <div class="bg-brand-primary h-2.5 rounded-full w-full animate-pulse"></div>
                </div>
            </div>

            <div x-show="step === 4" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 4: Análisis Completo</h2>
                <p class="text-brand-text-dim mb-6">Encontramos <strong class="text-brand-text" x-text="analysisResult?.total_repos"></strong> repositorios. Estos son tus lenguajes principales:</p>
                <ul class="space-y-3">
                    <template x-for="[lenguaje, conteo] in Object.entries(analysisResult?.lenguajes || {})" :key="lenguaje">
                        <li class="flex justify-between items-center p-3 bg-brand-bg/50 border border-brand-border/50 rounded-lg">
                            <span class="font-medium text-brand-text" x-text="lenguaje"></span>
                            <span class="text-sm text-brand-text-dim" x-text="`${conteo} repos`"></span>
                        </li>
                    </template>
                    <template x-if="!analysisResult || Object.keys(analysisResult.lenguajes).length === 0">
                        <li class="p-3 text-center text-brand-text-dim" style="display: none;">No se encontraron lenguajes en tus repositorios públicos.</li>
                    </template>
                </ul>
            </div>

            <div x-show="step === 5" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 5: Tus Habilidades</h2>
                <p class="text-brand-text-dim mb-6">Hemos pre-seleccionado las que encontramos en tu análisis. ¡Añade o quita las que quieras!</p>
                <div class="space-y-6">
                    <template x-for="(tecnologias, categoria) in techLibrary" :key="categoria">
                        <div class="relative">
                            <h3 class="text-lg font-semibold text-brand-primary mb-3" x-text="categoria"></h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <template x-for="tech in tecnologias" :key="tech.nombre">
                                    <label :class="selectedTech.includes(tech.nombre) ? 'border-brand-primary bg-brand-primary/10' : 'border-brand-border hover:border-brand-border/50'" class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer transition-all duration-200">
                                        <input type="checkbox" :value="tech.nombre" x-model="selectedTech" class="hidden">
                                        <img :src="tech.url_insignia" alt="" class="h-5">
                                        <span :class="selectedTech.includes(tech.nombre) ? 'text-brand-text' : 'text-brand-text-dim'" class="text-sm font-medium" x-text="tech.nombre"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="step === 6" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 6: Sugerencias de Biografía</h2>
                <p class="text-brand-text-dim mb-6">
                    Basado en tus habilidades, nuestra IA (simulada) sugiere estas opciones:
                </p>
                <div x-show="error" class="mb-4 p-3 bg-red-800/20 border border-red-500/30 rounded-lg text-red-400" x-text="error" style="display: none;"></div>

                <div x-show="bioLoading" class="flex justify-center items-center h-48">
                    <div class="w-8 h-8 border-4 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div x-show="!bioLoading" class="space-y-4" style="display: none;">
                    <template x-for="(bio, index) in bioSuggestions" :key="index">
                         <label 
                            :class="selectedBio === bio ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'"
                            class="block p-4 border rounded-lg cursor-pointer transition-all">
                            <input type="radio" name="bio_option" x-model="selectedBio" :value="bio" class="hidden">
                            <p class="text-brand-text" x-text="bio"></p>
                        </label>
                    </template>
                    
                    <label 
                        :class="!bioSuggestions.includes(selectedBio) ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'"
                        class="block p-4 border rounded-lg cursor-pointer transition-all">
                        <input type="radio" name="bio_option" x-model="selectedBio" :value="selectedBio" @click="selectedBio = ''" class="hidden">
                        <p class="text-brand-text-dim mb-2">O escribe la tuya:</p>
                        <textarea 
                            x-model="selectedBio"
                            @focus="bioSuggestions.includes(selectedBio) ? selectedBio = '' : null"
                            class="w-full p-2 bg-brand-bg border border-brand-border rounded-lg text-brand-text focus:outline-none focus:ring-0"
                            rows="3"
                            placeholder="Desarrollador web con base en México..."></textarea>
                    </label>
                </div>
            </div>

            <div x-show="step === 7" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">Paso 7: Elige un Estilo</h2>
                <p class="text-brand-text-dim mb-6">Elige cómo quieres que se muestre tu perfil.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div 
                        @click="selectedTemplate = 'tecnologico'" 
                        :class="selectedTemplate === 'tecnologico' ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'" 
                        class="p-4 border rounded-lg cursor-pointer transition-all">
                        <h3 class="text-lg font-semibold text-brand-text">Tecnológico</h3>
                        <p class="text-sm text-brand-text-dim">Insignias y estadísticas.</p>
                    </div>
                    <div 
                        @click="selectedTemplate = 'corporativo'" 
                        :class="selectedTemplate === 'corporativo' ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'" 
                        class="p-4 border rounded-lg cursor-pointer transition-all">
                        <h3 class="text-lg font-semibold text-brand-text">Corporativo</h3>
                        <p class="text-sm text-brand-text-dim">Limpio y basado en listas.</p>
                    </div>
                    <div 
                        @click="selectedTemplate = 'minimalista'" 
                        :class="selectedTemplate === 'minimalista' ? 'border-brand-primary ring-2 ring-brand-primary' : 'border-brand-border hover:border-brand-border/50'" 
                        class="p-4 border rounded-lg cursor-pointer transition-all">
                        <h3 class="text-lg font-semibold text-brand-text">Minimalista</h3>
                        <p class="text-sm text-brand-text-dim">Solo texto.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 8" class="p-6 md:p-10" style="display: none;">
                <h2 class="text-2xl font-semibold text-brand-text mb-2">¡Tu perfil está listo!</h2>
                <p class="text-brand-text-dim mb-4">Copia este código y pégalo en el `README.md` de tu repositorio de perfil de GitHub.</p>
                <textarea 
                    readonly 
                    x-text="finalMarkdown" 
                    class="w-full h-96 p-4 bg-brand-bg border border-brand-border rounded-lg text-brand-text font-mono text-sm whitespace-pre overflow-auto focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary"
                ></textarea>
            </div>


            <footer class="bg-brand-bg/50 border-t border-brand-border px-6 py-4 flex justify-between items-center">
                
                <button 
                    x-show="step === 2 || step === 4 || step === 5 || step === 6 || step === 7 || step === 8"
                    @click="step--"
                    style="display: none;"
                    class="px-5 py-2 bg-brand-surface border border-brand-border rounded-lg font-semibold text-brand-text-dim hover:text-brand-text hover:border-brand-text/50 transition-all duration-300">
                    Atrás
                </button>

                <button 
                    x-show="step === 1"
                    @click="step = 2"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto">
                    Siguiente
                </button>

                <button 
                    x-show="step === 2"
                    @click="analizarPerfil()"
                    :disabled="githubUser.trim() === ''"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100"
                    style="display: none;">
                    Analizar
                </button>

                <button 
                    x-show="step === 4"
                    @click="step = 5"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto"
                    style="display: none;">
                    Elegir mis habilidades
                </button>

                <button 
                    x-show="step === 5"
                    @click="generarBios()"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto"
                    style="display: none;">
                    Siguiente
                </button>

                <button 
                    x-show="step === 6"
                    @click="step = 7"
                    :disabled="bioLoading"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto disabled:opacity-50"
                    style="display: none;">
                    Siguiente
                </button>

                <button 
                    x-show="step === 7"
                    @click="generarCodigo()"
                    class="px-5 py-2 bg-green-600 rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto"
                    style="display: none;">
                    Generar mi Código
                </button>

                <button 
                    x-show="step === 8"
                    @click="navigator.clipboard.writeText(finalMarkdown)"
                    class="px-5 py-2 bg-brand-primary rounded-lg font-semibold text-white/90 hover:bg-opacity-80 transition-all duration-300 transform hover:scale-105 shadow-lg ml-auto"
                    style="display: none;">
                    Copiar Código
                </button>

            </footer>
        </div>
    </main>
    
    <footer class="w-full max-w-6xl mx-auto text-center">
        <p class="text-sm text-brand-text-dim">
            Creado con ❤️ por <span class="font-semibold text-brand-text">JohnManrique900</span>
        </p>
    </footer>

</body>
</html>