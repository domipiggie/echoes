<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
  isOpen: Boolean,
  position: Object
});

const emit = defineEmits(['select-gif', 'close']);
const gifs = ref([]);
const searchQuery = ref('');
const loading = ref(false);
const GIPHY_KEY = 'GlVGYHkr3WSBnllca54iNt0yFbjz7L65';
const debounceTimeout = ref(null);

// Előre betöltött trending GIF-ek
const cachedTrendingGifs = ref([]);

// Debounce funkció a kereséshez
const debouncedSearch = (query) => {
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }

  debounceTimeout.value = setTimeout(() => {
    performSearch(query);
  }, 300);
};

const performSearch = async (query) => {
  // Ha üres a keresés és van már cache-elt trending GIF, használjuk azt
  if (!query && cachedTrendingGifs.value.length > 0) {
    gifs.value = cachedTrendingGifs.value;
    return;
  }

  loading.value = true;
  try {
    const response = await fetch(
      `https://api.giphy.com/v1/gifs/search?api_key=${GIPHY_KEY}&q=${query || 'trending'}&limit=20`
    );
    const data = await response.json();
    gifs.value = data.data || [];

    // Cache-eljük a trending GIF-eket
    if (!query) {
      cachedTrendingGifs.value = gifs.value;
    }
  } catch (error) {
    console.error('Error fetching GIFs:', error);
    gifs.value = [];
  } finally {
    loading.value = false;
  }
};

// Optimalizált GIF képek URL-jei
const optimizedGifs = computed(() => {
  return gifs.value.map(gif => ({
    ...gif,
    optimizedUrl: gif.images.fixed_height_small.url // Kisebb méretű kép a gyorsabb betöltésért
  }));
});

const searchGifs = () => {
  debouncedSearch(searchQuery.value);
};

const selectGif = (gif) => {
  if (gif && gif.images) {
    emit('select-gif', gif.images.original.url);
    emit('close');
  }
};

onMounted(() => {
  performSearch('');
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }
});

const handleClickOutside = (event) => {
  if (!event.target.closest('.gif-picker')) {
    emit('close');
  }
};
</script>

<template>
  <div class="gif-picker">
    <div class="gif-search">
      <input type="text" v-model="searchQuery" @input="searchGifs" placeholder="Keresés..." class="gif-search-input" />
    </div>
    <div class="gif-grid">
      <div v-if="loading" class="loading">Betöltés...</div>
      <div v-else-if="optimizedGifs.length === 0" class="no-results">Nincs találat</div>
      <div v-else v-for="gif in optimizedGifs" :key="gif.id" class="gif-item" @click="selectGif(gif)">
        <img :src="gif.images.fixed_height_small.url" :alt="gif.title" loading="lazy" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.gif-picker {
  position: absolute;
  bottom: 60px;
  left: 20px;
  min-width: 200px;
  max-width: 400px;
  width: 100%;
  border-radius: 6px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  background: #ffffff;
  overflow: hidden;

  .dark-mode & {
    background: #1e1e2d;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
}

.gif-search {
  padding: 8px;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
}

:global(.dark-mode) .gif-search {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.gif-search-input {
  width: 100%;
  padding: 6px 10px;
  background: #f1f5f9;
  border: none;
  border-radius: 4px;
  color: #2d3748;
}

:global(.dark-mode) .gif-search-input {
  background: #151521;
  color: #e0e0e0;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.gif-grid {
  padding: 8px;
  min-height: 200px;
  max-height: 400px;
  height: 100%;
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

.gif-grid::-webkit-scrollbar {
  display: none;
  width: 0;
}

.gif-item {
  margin-bottom: 8px;
  cursor: pointer;
  max-width: 49%;
  width: 100%;
}

.gif-item:nth-child(2n) {
  margin-left: 2%;
}

.gif-item img {
  width: 100%;
  border-radius: 4px;
  transition: opacity 0.2s;
  will-change: transform;
}

.loading,
.no-results {
  padding: 20px;
  text-align: center;
  color: #a0aec0;
}

:global(.dark-mode) .loading,
:global(.dark-mode) .no-results {
  color: #9899ac;
}

/* Reszponzív stílusok */
@media (max-width: 768px) {
  .gif-picker {
    width: 250px;
    max-width: 35vw;
  }

  .gif-grid {
    max-height: 250px;
  }

  .gif-item {
    min-width: 100%;
    width: 100%;
  }

  .gif-item:nth-child(2n) {
    margin-left: 0;
  }
}

@media (max-width: 480px) {
  .gif-picker {
    width: 220px;
    max-width: 30vw;
  }

  .gif-grid {
    max-height: 200px;
  }

  .gif-search-input {
    padding: 4px 8px;
    font-size: 14px;
  }
}
</style>