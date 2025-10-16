'use client';

import axios from "axios";

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      // Only access localStorage in browser environment
      if (typeof localStorage !== "undefined") {
        localStorage.removeItem("bukupasar_token");
      }

      delete api.defaults.headers.common.Authorization;
    }

    return Promise.reject(error);
  },
);

export const setAuthToken = (token: string | null) => {
  if (!token) {
    delete api.defaults.headers.common.Authorization;
    return;
  }

  api.defaults.headers.common.Authorization = `Bearer ${token}`;
};

export default api;
