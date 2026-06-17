<template>
  <canvas 
    ref="canvasRef" 
    class="w-full h-full touch-none"
    @mousedown="startDrawing"
    @mousemove="draw"
    @mouseup="stopDrawing"
    @mouseleave="stopDrawing"
    @touchstart.prevent="startDrawing"
    @touchmove.prevent="draw"
    @touchend.prevent="stopDrawing"
  ></canvas>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: true }
});

const emit = defineEmits(['update:hasSignature', 'update:modelValue']);

const canvasRef = ref(null);
const isDrawing = ref(false);
const hasSignature = ref(false);
let ctx = null;

const initCanvas = async () => {
  await nextTick();
  const canvas = canvasRef.value;
  if (!canvas) return;
  
  ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width;
  canvas.height = rect.height;
  
  ctx.strokeStyle = '#1e293b'; 
  ctx.lineWidth = 3;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

watch(() => props.show, async (newVal) => {
  if (newVal) {
    hasSignature.value = false;
    emit('update:hasSignature', false);
    setTimeout(initCanvas, 150); 
  }
});

onMounted(() => {
  if (props.show) {
    setTimeout(initCanvas, 150);
  }
});

const getPos = (e) => {
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width;
  const scaleY = canvas.height / rect.height;
  const clientX = e.touches ? e.touches[0].clientX : e.clientX;
  const clientY = e.touches ? e.touches[0].clientY : e.clientY;
  return {
    x: (clientX - rect.left) * scaleX,
    y: (clientY - rect.top) * scaleY
  };
};

const startDrawing = (e) => {
  if (!ctx) return;
  isDrawing.value = true;
  const pos = getPos(e);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
  if (!isDrawing.value || !ctx) return;
  const pos = getPos(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.stroke();
  hasSignature.value = true;
  emit('update:hasSignature', true);
};

const stopDrawing = () => {
  isDrawing.value = false;
  if (ctx) ctx.closePath();
  if (hasSignature.value && canvasRef.value) {
    emit('update:modelValue', canvasRef.value.toDataURL('image/png'));
  }
};

const clearSignature = () => {
  if (!ctx || !canvasRef.value) return;
  ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  hasSignature.value = false;
  ctx.beginPath();
  emit('update:hasSignature', false);
  emit('update:modelValue', '');
};

const getSignatureData = () => {
    return (hasSignature.value && canvasRef.value) ? canvasRef.value.toDataURL('image/png') : null;
};

defineExpose({
  clearSignature,
  getSignatureData,
  hasSignature: () => hasSignature.value
});
</script>
