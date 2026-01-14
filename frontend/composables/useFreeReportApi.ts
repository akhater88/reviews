import { ref } from 'vue'

export interface Place {
  place_id: string
  name: string
  formatted_address: string
  rating?: number
  user_ratings_total?: number
}

interface SearchResponse {
  success: boolean
  places: Place[]
  message?: string
}

interface ApiResponse {
  success: boolean
  message?: string
  data?: any
}

interface ReportStatusResponse {
  success: boolean
  data?: {
    status: string
    progress: number
    token?: string
    error_message?: string
  }
}

export function useFreeReportApi() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || ''

  const isLoading = ref(false)

  // Search places
  const searchPlaces = async (query: string, location?: string): Promise<SearchResponse> => {
    isLoading.value = true

    try {
      const params: Record<string, string> = { query }
      if (location) {
        params.location = location
      }

      const response = await $fetch<SearchResponse>(`${apiBase}/api/free-report/places/search`, {
        params,
      })

      return response
    } catch (error: any) {
      return {
        success: false,
        places: [],
        message: error.data?.message || 'فشل في البحث'
      }
    } finally {
      isLoading.value = false
    }
  }

  // Request OTP
  const requestOtp = async (phone: string, name: string): Promise<ApiResponse> => {
    isLoading.value = true

    try {
      const response = await $fetch<ApiResponse>(`${apiBase}/api/free-report/request-otp`, {
        method: 'POST',
        body: { phone, name },
      })

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  // Verify OTP
  const verifyOtp = async (phone: string, otp: string): Promise<ApiResponse> => {
    isLoading.value = true

    try {
      const response = await $fetch<ApiResponse>(`${apiBase}/api/free-report/verify-otp`, {
        method: 'POST',
        body: { phone, otp },
      })

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  // Resend OTP
  const resendOtp = async (phone: string): Promise<ApiResponse> => {
    isLoading.value = true

    try {
      const response = await $fetch<ApiResponse>(`${apiBase}/api/free-report/resend-otp`, {
        method: 'POST',
        body: { phone },
      })

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  // Create free report
  const createFreeReport = async (data: {
    place_id: string
    place_name: string
    phone: string
    name: string
    email?: string
  }): Promise<ApiResponse> => {
    isLoading.value = true

    try {
      const response = await $fetch<ApiResponse>(`${apiBase}/api/free-report/create`, {
        method: 'POST',
        body: data,
      })

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  // Check report status
  const checkReportStatus = async (phone: string, placeId: string): Promise<ReportStatusResponse> => {
    try {
      const response = await $fetch<ReportStatusResponse>(`${apiBase}/api/free-report/status`, {
        params: { phone, place_id: placeId },
      })

      return response
    } catch (error: any) {
      return {
        success: false,
        data: {
          status: 'error',
          progress: 0,
          error_message: error.data?.message || 'فشل في التحقق من الحالة',
        },
      }
    }
  }

  return {
    isLoading,
    searchPlaces,
    requestOtp,
    verifyOtp,
    resendOtp,
    createFreeReport,
    checkReportStatus,
  }
}
