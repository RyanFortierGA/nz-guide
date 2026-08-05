#!/usr/bin/env node
/**
 * Enrich locations.json: Unsplash images, Google Maps links, extra flying subspots.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const file = path.join(__dirname, 'locations.json');
const data = JSON.parse(fs.readFileSync(file, 'utf8'));

const IMAGES = {
  queenstown: ['https://images.unsplash.com/photo-1578271881656-fe9fac4055ba?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1400&q=80'],
  brisbane: ['https://images.unsplash.com/photo-1523482580671-d19b5e9b5c34?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1400&q=80'],
  sydney: ['https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1523428096881-5bd79d043006?auto=format&fit=crop&w=1400&q=80'],
  fiji: ['https://images.unsplash.com/photo-1589197331516-4d84b72eb2ad?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1400&q=80'],
  taupo: ['https://images.unsplash.com/photo-1605640840607-3ee222dfbae6?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1400&q=80'],
  rotorua: ['https://images.unsplash.com/photo-1589802829985-817e51170b0d?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=80'],
  tauranga: ['https://images.unsplash.com/photo-1559827260-dc66d52bef19?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80'],
  hobbiton: ['https://images.unsplash.com/photo-1555881400-74d7acaacd8b?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=80'],
  bayofislands: ['https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?auto=format&fit=crop&w=1400&q=80'],
  putaruru: ['https://images.unsplash.com/photo-1439066615861-d1af74d74000?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1501785888041-af3ef9619453?auto=format&fit=crop&w=1400&q=80'],
  tarawhanui: ['https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1400&q=80'],
  duder: ['https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1501785888041-af3ef9619453?auto=format&fit=crop&w=1400&q=80'],
  piha: ['https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80'],
  waiheke: ['https://images.unsplash.com/photo-1555899434-94d1368aa7af?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1400&q=80'],
  takapuna: ['https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1400&q=80'],
  cornwall: ['https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=80'],
  missionbay: ['https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1400&q=80'],
  devonport: ['https://images.unsplash.com/photo-1523482580671-d19b5e9b5c34?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1400&q=80'],
  albertpark: ['https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1519331379826-f10be5486c6f?auto=format&fit=crop&w=1400&q=80'],
  artgallery: ['https://images.unsplash.com/photo-1578662996442-48f60103fc96?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?auto=format&fit=crop&w=1400&q=80'],
  commercialbay: ['https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1400&q=80'],
  civic: ['https://images.unsplash.com/photo-1518998053901-5348d3961a04?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80'],
  viaduct: ['https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1400&q=80'],
  skytower: ['https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&q=80'],
  wintergardens: ['https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=1400&q=80', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=80'],
};

const SUB_IMAGES = {
  'Skyline Gondola': 'https://images.unsplash.com/photo-1578271881656-fe9fac4055ba?auto=format&fit=crop&w=900&q=80',
  'Shotover Jet': 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=900&q=80',
  Wanaka: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=80',
  'Milford Sound': 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=900&q=80',
  'Remarkables': 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
  'Australia Zoo': 'https://images.unsplash.com/photo-1564349683136-77e08bbbd8ea?auto=format&fit=crop&w=900&q=80',
  'Story Bridge': 'https://images.unsplash.com/photo-1523482580671-d19b5e9b5c34?auto=format&fit=crop&w=900&q=80',
  'Gold Coast': 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
  'Sunshine Coast': 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=900&q=80',
  Tangalooma: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=900&q=80',
  'Sydney Opera House': 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=900&q=80',
  'Taronga Zoo': 'https://images.unsplash.com/photo-1564349683136-77e08bbbd8ea?auto=format&fit=crop&w=900&q=80',
  'Bondi Beach': 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
  Manly: 'https://images.unsplash.com/photo-1523482580671-d19b5e9b5c34?auto=format&fit=crop&w=900&q=80',
  'Cloud 9': 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=900&q=80',
  'Mamanuca Islands': 'https://images.unsplash.com/photo-1589197331516-4d84b72eb2ad?auto=format&fit=crop&w=900&q=80',
  'Shark Dive': 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=900&q=80',
  Denarau: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
  'Huka Falls': 'https://images.unsplash.com/photo-1439066615861-d1af74d74000?auto=format&fit=crop&w=900&q=80',
  'Skyline Rotorua': 'https://images.unsplash.com/photo-1578271881656-fe9fac4055ba?auto=format&fit=crop&w=900&q=80',
  'Mauao Summit Track': 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=80',
  'Waitangi Treaty Grounds': 'https://images.unsplash.com/photo-1501785888041-af3ef9619453?auto=format&fit=crop&w=900&q=80',
  'Kitekite Falls': 'https://images.unsplash.com/photo-1439066615861-d1af74d74000?auto=format&fit=crop&w=900&q=80',
  'Mercer Bay Loop': 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=900&q=80',
};

function mapsUrl(name, lat, lng) {
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${name}@${lat},${lng}`)}`;
}

data.locations = data.locations.map((loc) => {
  const imgs = IMAGES[loc.slug];
  return {
    ...loc,
    image_url: imgs?.[0] || loc.image_url,
    image_url_2: imgs?.[1] || loc.image_url_2,
    maps_url: mapsUrl(loc.name, loc.lat, loc.lng),
  };
});

const EXTRA_SUBS = [
  { parent: 'queenstown', name: 'Wanaka', lat: -44.6942, lng: 169.1417 },
  { parent: 'queenstown', name: 'Milford Sound', lat: -44.6710, lng: 167.9250 },
  { parent: 'queenstown', name: 'Remarkables', lat: -45.0700, lng: 168.8100 },
  { parent: 'brisbane', name: 'Gold Coast', lat: -28.0167, lng: 153.4000 },
  { parent: 'brisbane', name: 'Sunshine Coast', lat: -26.6500, lng: 153.0667 },
  { parent: 'brisbane', name: 'Tangalooma', lat: -27.1790, lng: 153.3700 },
  { parent: 'sydney', name: 'Bondi Beach', lat: -33.8915, lng: 151.2767 },
  { parent: 'sydney', name: 'Manly', lat: -33.7969, lng: 151.2840 },
  { parent: 'fiji', name: 'Cloud 9', lat: -17.7800, lng: 177.1600 },
  { parent: 'fiji', name: 'Mamanuca Islands', lat: -17.6667, lng: 177.0833 },
  { parent: 'fiji', name: 'Shark Dive', lat: -18.1667, lng: 177.4500 },
  { parent: 'fiji', name: 'Denarau', lat: -17.7700, lng: 177.3800 },
];

const existingKeys = new Set(data.sub_locations.map((s) => `${s.parent}|${s.name}`));
for (const sub of EXTRA_SUBS) {
  const key = `${sub.parent}|${sub.name}`;
  if (!existingKeys.has(key)) {
    data.sub_locations.push(sub);
    existingKeys.add(key);
  }
}

data.sub_locations = data.sub_locations.map((s) => ({
  ...s,
  image_url: SUB_IMAGES[s.name] || s.image_url,
  maps_url: mapsUrl(s.name, s.lat, s.lng),
}));

fs.writeFileSync(file, JSON.stringify(data, null, 2));
console.log('locations', data.locations.length, 'subs', data.sub_locations.length);
